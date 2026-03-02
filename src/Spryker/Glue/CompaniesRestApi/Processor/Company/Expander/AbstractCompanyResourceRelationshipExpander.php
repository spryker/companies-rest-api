<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CompaniesRestApi\Processor\Company\Expander;

use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\RestCompanyAttributesTransfer;
use Spryker\Glue\CompaniesRestApi\Processor\Company\Mapper\CompanyMapperInterface;
use Spryker\Glue\CompaniesRestApi\Processor\Company\RestResponseBuilder\CompanyRestResponseBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

abstract class AbstractCompanyResourceRelationshipExpander implements CompanyResourceRelationshipExpanderInterface
{
    /**
     * @var \Spryker\Glue\CompaniesRestApi\Processor\Company\RestResponseBuilder\CompanyRestResponseBuilderInterface
     */
    protected $companyRestResponseBuilder;

    /**
     * @var \Spryker\Glue\CompaniesRestApi\Processor\Company\Mapper\CompanyMapperInterface
     */
    protected $companyMapper;

    public function __construct(
        CompanyRestResponseBuilderInterface $companyRestResponseBuilder,
        CompanyMapperInterface $companyMapper
    ) {
        $this->companyRestResponseBuilder = $companyRestResponseBuilder;
        $this->companyMapper = $companyMapper;
    }

    /**
     * @param array<\Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface> $resources
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return array<\Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface>
     */
    public function addResourceRelationships(array $resources, RestRequestInterface $restRequest): array
    {
        foreach ($resources as $resource) {
            $companyTransfer = $this->findCompanyTransferInPayload($resource);
            if (!$companyTransfer) {
                continue;
            }

            $resource->addRelationship(
                $this->createCompanyRestResourceFromCompanyTransfer($companyTransfer),
            );
        }

        return $resources;
    }

    abstract protected function findCompanyTransferInPayload(RestResourceInterface $restResource): ?CompanyTransfer;

    protected function createCompanyRestResourceFromCompanyTransfer(CompanyTransfer $companyTransfer): RestResourceInterface
    {
        $restCompanyAttributesTransfer = $this->companyMapper
            ->mapCompanyTransferToRestCompanyAttributesTransfer(
                $companyTransfer,
                new RestCompanyAttributesTransfer(),
            );

        return $this->companyRestResponseBuilder->createCompanyRestResource(
            $companyTransfer->getUuid(),
            $restCompanyAttributesTransfer,
        );
    }
}
