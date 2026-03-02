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

    public function __construct(
        CompaniesRestApiToCompanyClientInterface $companyClient,
        CompanyMapperInterface $companyMapperInterface,
        CompanyRestResponseBuilderInterface $companyRestResponseBuilder
    ) {
        $this->companyClient = $companyClient;
        $this->companyMapperInterface = $companyMapperInterface;
        $this->companyRestResponseBuilder = $companyRestResponseBuilder;
    }

    public function getCurrentUserCompany(RestRequestInterface $restRequest): RestResponseInterface
    {
        if ($this->isResourceIdentifierCurrentUser($restRequest->getResource()->getId())) {
            return $this->getCurrentUserCompanies($restRequest);
        }

        return $this->getCurrentUserCompanyByUuid($restRequest);
    }

    protected function getCurrentUserCompanies(RestRequestInterface $restRequest): RestResponseInterface
    {
        if (!$restRequest->getRestUser()->getIdCompany()) {
            return $this->companyRestResponseBuilder->createCompanyUserNotSelectedError();
        }

        $companyTransfer = $this->companyClient->getCompanyById(
            (new CompanyTransfer())->setIdCompany($restRequest->getRestUser()->getIdCompany()),
        );

        if (!$companyTransfer->getUuid()) {
            return $this->companyRestResponseBuilder->createCompanyNotFoundError();
        }

        return $this->createResponse($companyTransfer);
    }

    protected function getCurrentUserCompanyByUuid(RestRequestInterface $restRequest): RestResponseInterface
    {
        $companyResponseTransfer = $this->companyClient->findCompanyByUuid(
            (new CompanyTransfer())->setUuid($restRequest->getResource()->getId()),
        );

        if (
            !$companyResponseTransfer->getIsSuccessful()
            || !$this->isCurrentCompanyUserInCompany($restRequest, $companyResponseTransfer->getCompanyTransfer())
        ) {
            return $this->companyRestResponseBuilder->createCompanyNotFoundError();
        }

        return $this->createResponse($companyResponseTransfer->getCompanyTransfer());
    }

    protected function isResourceIdentifierCurrentUser(string $resourceIdentifier): bool
    {
        return $resourceIdentifier === CompaniesRestApiConfig::COLLECTION_IDENTIFIER_CURRENT_USER;
    }

    protected function createResponse(CompanyTransfer $companyTransfer): RestResponseInterface
    {
        $restCompanyAttributesTransfer = $this->companyMapperInterface
            ->mapCompanyTransferToRestCompanyAttributesTransfer(
                $companyTransfer,
                new RestCompanyAttributesTransfer(),
            );

        return $this->companyRestResponseBuilder
            ->createCompanyRestResponse(
                $companyTransfer->getUuid(),
                $restCompanyAttributesTransfer,
            );
    }

    protected function isCurrentCompanyUserInCompany(
        RestRequestInterface $restRequest,
        CompanyTransfer $companyTransfer
    ): bool {
        return $restRequest->getRestUser()
            && $restRequest->getRestUser()->getIdCompany()
            && $restRequest->getRestUser()->getIdCompany() === $companyTransfer->getIdCompany();
    }
}
