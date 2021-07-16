<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AuthBackendRestApi\Plugin\GlueApplication;

use Generated\Shared\Transfer\OauthAccessTokenDataTransfer;
use Generated\Shared\Transfer\RestUserTransfer;
use Spryker\Glue\AuthRestApi\AuthRestApiConfig;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\RestUserFinderPluginInterface;
use Spryker\Glue\Kernel\AbstractPlugin;
use Spryker\Service\Oauth\OauthService;
use Spryker\Service\UtilEncoding\UtilEncodingService;

class BackendRestUserFinderByAccessTokenPlugin extends AbstractPlugin implements RestUserFinderPluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Generated\Shared\Transfer\RestUserTransfer|null
     */
    public function findUser(RestRequestInterface $restRequest): ?RestUserTransfer
    {
        $authorizationToken = $restRequest->getHttpRequest()->headers->get(AuthRestApiConfig::HEADER_AUTHORIZATION);

        if (!$authorizationToken) {
            return null;
        }

        $oauthAccessTokenDataTransfer = $this->findUserByAccessToken((string)$authorizationToken);

        return $this->findRestUserTransfer($restRequest, $oauthAccessTokenDataTransfer);
    }

    /**
     * @param string $authorizationToken
     *
     * @return \Generated\Shared\Transfer\OauthAccessTokenDataTransfer
     */
    protected function findUserByAccessToken(string $authorizationToken): OauthAccessTokenDataTransfer
    {
        [$type, $accessToken] = $this->extractToken($authorizationToken);

        return (new OauthService())->extractAccessTokenData($accessToken);
    }

    /**
     * @param string $authorizationToken
     *
     * @return array
     */
    protected function extractToken(string $authorizationToken): array
    {
        return preg_split('/\s+/', $authorizationToken);
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     * @param \Generated\Shared\Transfer\OauthAccessTokenDataTransfer $oauthAccessTokenDataTransfer
     *
     * @return \Generated\Shared\Transfer\RestUserTransfer|null
     */
    protected function findRestUserTransfer(
        RestRequestInterface $restRequest,
        OauthAccessTokenDataTransfer $oauthAccessTokenDataTransfer
    ): ?RestUserTransfer {
        if (!$oauthAccessTokenDataTransfer->getOauthUserId()) {
            return null;
        }

        return $this->mapRestUserTransfer($oauthAccessTokenDataTransfer, $restRequest);
    }

    /**
     * @param \Generated\Shared\Transfer\OauthAccessTokenDataTransfer $oauthAccessTokenDataTransfer
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Generated\Shared\Transfer\RestUserTransfer
     */
    protected function mapRestUserTransfer(
        OauthAccessTokenDataTransfer $oauthAccessTokenDataTransfer,
        RestRequestInterface $restRequest
    ): RestUserTransfer {
        $customerIdentifier = (new UtilEncodingService())->decodeJson(
            $oauthAccessTokenDataTransfer->getOauthUserId(),
            true
        );

        $restUserTransfer = (new RestUserTransfer())
            ->fromArray($customerIdentifier, true)
            ->setNaturalIdentifier($customerIdentifier['username'])
            ->setSurrogateIdentifier($customerIdentifier['id']);

        return $restUserTransfer;
    }
}
