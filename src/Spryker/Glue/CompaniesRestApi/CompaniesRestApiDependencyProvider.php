<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CompaniesRestApi;

use Spryker\Glue\CompaniesRestApi\Dependency\Client\CompaniesRestApiToCompanyClientBridge;
use Spryker\Glue\Kernel\AbstractBundleDependencyProvider;
use Spryker\Glue\Kernel\Container;

/**
 * @method \Spryker\Glue\CompaniesRestApi\CompaniesRestApiConfig getConfig()
 */
class CompaniesRestApiDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const CLIENT_COMPANY = 'CLIENT_COMPANY';

    public function provideDependencies(Container $container): Container
    {
        $container = parent::provideDependencies($container);
        $container = $this->addCompanyClient($container);

        return $container;
    }

    protected function addCompanyClient(Container $container): Container
    {
        $container->set(static::CLIENT_COMPANY, function (Container $container) {
            return new CompaniesRestApiToCompanyClientBridge(
                $container->getLocator()->company()->client(),
            );
        });

        return $container;
    }
}
