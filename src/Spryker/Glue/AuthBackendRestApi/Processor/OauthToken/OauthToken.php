<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AuthBackendRestApi\Processor\OauthToken;

use Generated\Shared\Transfer\OauthRequestTransfer;
use Spryker\Glue\AuthBackendRestApi\Dependency\Facade\AuthBackendRestApiToOauthFacadeInterface;
use Spryker\Zed\OauthUser\OauthUserConfig;
use Symfony\Component\HttpFoundation\JsonResponse;

class OauthToken implements OauthTokenInterface
{
    /**
     * @var array
     */
    protected const ALLOWED_GRANT_TYPES = [
        OauthUserConfig::GRANT_TYPE_USER_CREDENTIALS,
    ];

    /**
     * @var \Spryker\Glue\AuthBackendRestApi\Dependency\Facade\AuthBackendRestApiToOauthFacadeInterface
     */
    protected $oauthFacade;

    /**
     * @param \Spryker\Glue\AuthBackendRestApi\Dependency\Facade\AuthBackendRestApiToOauthFacadeInterface $oauthFacade
     */
    public function __construct(AuthBackendRestApiToOauthFacadeInterface $oauthFacade)
    {
        $this->oauthFacade = $oauthFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\OauthRequestTransfer $oauthRequestTransfer
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createAccessToken(OauthRequestTransfer $oauthRequestTransfer): JsonResponse
    {
        $response = new JsonResponse();
        if (!in_array($oauthRequestTransfer->getGrantType(), static::ALLOWED_GRANT_TYPES, true)) {
            return $response->setData([
                'error' => 'invalid_grant',
                'error_description' => 'The provided authorization grant (e.g., authorization code, resource owner credentials) or refresh token '
                    . 'is invalid, expired, revoked, does not match the redirection URI used in the authorization request, '
                    . 'or was issued to another client.',
            ])->setStatusCode(400);
        }

        $oauthResponseTransfer = $this->oauthFacade->processAccessTokenRequest($oauthRequestTransfer);
        if (!$oauthResponseTransfer->getIsValid()) {
            /**
             * @see https://tools.ietf.org/html/rfc6749#section-5.2
             */
            $response->setStatusCode(400);

            //This is added for BC reasons since Oauth module is not compliant with above RFC, this shim is needed
            //to make the API endpoint compliant until a major change updates Oauth
            if ($oauthResponseTransfer->getError()->getErrorType() === 'invalid_credentials') {
                return $response->setData([
                    'error' => 'invalid_grant',
                    'error_description' => 'The provided authorization grant (e.g., authorization code, resource owner credentials) or refresh token '
                        . 'is invalid, expired, revoked, does not match the redirection URI used in the authorization request, '
                        . 'or was issued to another client.',
                ]);
            }

            return $response->setData([
                'error' => $oauthResponseTransfer->getError()->getErrorType(),
                'error_description' => $oauthResponseTransfer->getError()->getMessage(),
            ]);
        }

        /**
         * @see https://tools.ietf.org/html/rfc6749#section-5.1
         */
        return $response->setData([
            'access_token' => $oauthResponseTransfer->getAccessToken(),
            'token_type' => $oauthResponseTransfer->getTokenType(),
            'expires_in' => $oauthResponseTransfer->getExpiresIn(),
            'refresh_token' => $oauthResponseTransfer->getRefreshToken(),
        ])->setStatusCode(200);
    }
}
