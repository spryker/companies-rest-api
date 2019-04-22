<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CompaniesRestApi\Processor\Company\Reader;

use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\RestCompanyAttributesTransfer;
use Spryker\Glue\CompaniesRestApi\CompaniesRestApiConfig;
use Spryker\Glue\CompaniesRestApi\Dependency\Client\CompaniesRestApiToCompanyClientInterface;
use Spryker\Glue\CompaniesRestApi\Processor\Company\Mapper\CompanyMapperInterface;
use Spryker\Glue\CompaniesRestApi\Processor\Company\RestResponseBuilder\CompanyRestResponseBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

class CompanyReader implements CompanyReaderInterface
{
    /**
     * @var \Spryker\Glue\CompaniesRestApi\Dependency\Client\CompaniesRestApiToCompanyClientInterface
     */
    protected $companyClient;

    /**
     * @var \Spryker\Glue\CompaniesRestApi\Processor\Company\Mapper\CompanyMapperInterface
     */
    protected $companyMapperInterface;

    /**
     * @var \Spryker\Glue\CompaniesRestApi\Processor\Company\RestResponseBuilder\CompanyRestResponseBuilderInterface
     */
    protected $companyRestResponseBuilder;

    /**
     * @param \Spryker\Glue\CompaniesRestApi\Dependency\Client\CompaniesRestApiToCompanyClientInterface $companyClient
     * @param \Spryker\Glue\CompaniesRestApi\Processor\Company\Mapper\CompanyMapperInterface $companyMapperInterface
     * @param \Spryker\Glue\CompaniesRestApi\Processor\Company\RestResponseBuilder\CompanyRestResponseBuilderInterface $companyRestResponseBuilder
     */
    public function __construct(
        CompaniesRestApiToCompanyClientInterface $companyClient,
        CompanyMapperInterface $companyMapperInterface,
        CompanyRestResponseBuilderInterface $companyRestResponseBuilder
    ) {
        $this->companyClient = $companyClient;
        $this->companyMapperInterface = $companyMapperInterface;
        $this->companyRestResponseBuilder = $companyRestResponseBuilder;
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function getCurrentUserCompany(RestRequestInterface $restRequest): RestResponseInterface
    {
        if (!$restRequest->getRestUser()->getIdCompany()) {
            return $this->companyRestResponseBuilder->createCompanyIdMissingError();
        }

        if (!$this->isCurrentUserResourceIdentifier($restRequest->getResource()->getId())) {
            return $this->companyRestResponseBuilder->createResourceNotImplementedError();
        }

        $companyTransfer = $this->companyClient->getCompanyById(
            (new CompanyTransfer())->setIdCompany($restRequest->getRestUser()->getIdCompany())
        );

        if (!$companyTransfer->getUuid()) {
            return $this->companyRestResponseBuilder->createCompanyNotFoundError();
        }

        return $this->createResponse($companyTransfer);
    }

    /**
     * @param string $companyIdentifier
     *
     * @return bool
     */
    protected function isCurrentUserResourceIdentifier(string $companyIdentifier): bool
    {
        return $companyIdentifier === CompaniesRestApiConfig::CURRENT_USER_RESOURCE_IDENTIFIER;
    }

    /**
     * @param \Generated\Shared\Transfer\CompanyTransfer $companyTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    protected function createResponse(CompanyTransfer $companyTransfer): RestResponseInterface
    {
        $restCompanyAttributesTransfer = $this->companyMapperInterface
            ->mapCompanyTransferToRestCompanyAttributesTransfer(
                $companyTransfer,
                new RestCompanyAttributesTransfer()
            );

        return $this->companyRestResponseBuilder
            ->createCompanyRestResponse(
                $companyTransfer->getUuid(),
                $restCompanyAttributesTransfer
            );
    }
}
