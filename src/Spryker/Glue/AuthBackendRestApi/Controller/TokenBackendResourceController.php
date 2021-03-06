<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AuthBackendRestApi\Controller;

use Generated\Shared\Transfer\OauthRequestTransfer;
use Spryker\Glue\Kernel\Backend\Controller\FormattedAbstractBackendController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Glue\AuthBackendRestApi\AuthBackendRestApiFactory getFactory()
 */
class TokenBackendResourceController extends FormattedAbstractBackendController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $httpRequest
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postAction(Request $httpRequest): JsonResponse
    {
        $oauthRequestTransfer = (new OauthRequestTransfer())->fromArray($httpRequest->request->all(), true);

        return $this->getFactory()->createOauthToken()->createAccessToken($oauthRequestTransfer);
    }
}
