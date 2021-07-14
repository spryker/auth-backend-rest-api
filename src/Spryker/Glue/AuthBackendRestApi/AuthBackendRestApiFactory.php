<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AuthBackendRestApi;

use Spryker\Glue\AuthBackendRestApi\Dependency\Facade\AuthBackendRestApiToOauthFacadeInterface;
use Spryker\Glue\AuthBackendRestApi\Processor\OauthToken\OauthToken;
use Spryker\Glue\AuthBackendRestApi\Processor\OauthToken\OauthTokenInterface;
use Spryker\Glue\Kernel\Backend\Factory\AbstractBackendFactory;

class AuthBackendRestApiFactory extends AbstractBackendFactory
{
    /**
     * @return \Spryker\Glue\AuthBackendRestApi\Processor\OauthToken\OauthTokenInterface
     */
    public function createOauthToken(): OauthTokenInterface
    {
        return new OauthToken($this->getOauthFacade());
    }

    /**
     * @return \Spryker\Glue\AuthBackendRestApi\Dependency\Facade\AuthBackendRestApiToOauthFacadeInterface
     */
    public function getOauthFacade(): AuthBackendRestApiToOauthFacadeInterface
    {
        return $this->getProvidedDependency(AuthBackendRestApiDependencyProvider::FACADE_OAUTH);
    }
}
