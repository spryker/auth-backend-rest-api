<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AuthBackendRestApi;

use Spryker\Glue\AuthBackendRestApi\Dependency\Facade\AuthBackendRestApiToOauthFacadeBridge;
use Spryker\Glue\Kernel\AbstractBundleDependencyProvider;
use Spryker\Glue\Kernel\Container;

/**
 * @method \Spryker\Glue\AuthBackendRestApi\AuthBackendRestApiConfig getConfig()
 */
class AuthBackendRestApiDependencyProvider extends AbstractBundleDependencyProvider
{
    public const FACADE_OAUTH = 'FACADE_OAUTH';

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    public function provideDependencies(Container $container): Container
    {
        $container = parent::provideDependencies($container);
        $container = $this->addOauthFacade($container);

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addOauthFacade(Container $container): Container
    {
        $container->set(static::FACADE_OAUTH, function (Container $container) {
            return new AuthBackendRestApiToOauthFacadeBridge($container->getLocator()->oauth()->facade());
        });

        return $container;
    }
}
