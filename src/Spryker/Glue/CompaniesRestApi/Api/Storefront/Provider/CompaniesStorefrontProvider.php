<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CompaniesRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\CompaniesStorefrontResource;
use Generated\Shared\Transfer\CompanyTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Glue\CompaniesRestApi\Api\Storefront\Mapper\CompanyResourceMapperInterface;
use Spryker\Glue\CompaniesRestApi\Api\Storefront\Reader\CompanyReaderInterface;
use Spryker\Glue\CompaniesRestApi\CompaniesRestApiConfig;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CompaniesStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string KEY_UUID = 'uuid';

    protected const string OPERATION_NAME_GET_COMPANIES_MINE = 'getCompaniesMine';

    /**
     * BC: legacy `GET /companies` (no id) returned a 501 error envelope without a
     * Spryker `code` field at all. We pass an empty string to {@see GlueApiException}
     * so the emitted error envelope matches the legacy contract (no `code` key value).
     */
    protected const string RESPONSE_CODE_RESOURCE_NOT_IMPLEMENTED = '';

    public function __construct(
        protected CompanyReaderInterface $companyReader,
        protected CompanyResourceMapperInterface $companyResourceMapper,
        protected SerializerServiceInterface $serializer,
    ) {
    }

    /**
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    protected function provideItem(): ?object
    {
        if (!$this->hasCustomer()) {
            throw new AccessDeniedException();
        }

        return $this->provideCompanyByUuid((string)$this->getUriVariable(static::KEY_UUID));
    }

    /**
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     *
     * @return array<\Generated\Api\Storefront\CompaniesStorefrontResource>
     */
    protected function provideCollection(): array
    {
        if ($this->getOperation()->getName() !== static::OPERATION_NAME_GET_COMPANIES_MINE) {
            throw new GlueApiException(
                Response::HTTP_NOT_IMPLEMENTED,
                static::RESPONSE_CODE_RESOURCE_NOT_IMPLEMENTED,
                CompaniesRestApiConfig::RESPONSE_DETAIL_RESOURCE_NOT_IMPLEMENTED,
            );
        }

        if (!$this->hasCustomer()) {
            throw new AccessDeniedException();
        }

        return [$this->provideCurrentUserCompany()];
    }

    protected function provideCurrentUserCompany(): CompaniesStorefrontResource
    {
        $idCompany = $this->resolveCurrentUserCompanyId();

        if ($idCompany === null) {
            throw new GlueApiException(
                Response::HTTP_FORBIDDEN,
                CompaniesRestApiConfig::RESPONSE_CODE_COMPANY_USER_NOT_SELECTED,
                CompaniesRestApiConfig::RESPONSE_DETAIL_COMPANY_USER_NOT_SELECTED,
            );
        }

        $companyTransfer = $this->companyReader->findCompanyByIdCompany($idCompany);

        if ($companyTransfer === null) {
            throw new GlueApiException(
                Response::HTTP_NOT_FOUND,
                CompaniesRestApiConfig::RESPONSE_CODE_COMPANY_NOT_FOUND,
                CompaniesRestApiConfig::RESPONSE_DETAIL_COMPANY_NOT_FOUND,
            );
        }

        return $this->denormalizeToResource($companyTransfer);
    }

    protected function provideCompanyByUuid(string $uuid): CompaniesStorefrontResource
    {
        $companyTransfer = $this->companyReader->findCompanyByUuid($uuid);

        if ($companyTransfer === null) {
            throw new GlueApiException(
                Response::HTTP_NOT_FOUND,
                CompaniesRestApiConfig::RESPONSE_CODE_COMPANY_NOT_FOUND,
                CompaniesRestApiConfig::RESPONSE_DETAIL_COMPANY_NOT_FOUND,
            );
        }

        $idCurrentCompany = $this->resolveCurrentUserCompanyId();

        if ($idCurrentCompany === null || $idCurrentCompany !== $companyTransfer->getIdCompany()) {
            throw new GlueApiException(
                Response::HTTP_NOT_FOUND,
                CompaniesRestApiConfig::RESPONSE_CODE_COMPANY_NOT_FOUND,
                CompaniesRestApiConfig::RESPONSE_DETAIL_COMPANY_NOT_FOUND,
            );
        }

        return $this->denormalizeToResource($companyTransfer);
    }

    protected function resolveCurrentUserCompanyId(): ?int
    {
        return $this->getCustomer()->getCompanyUserTransfer()?->getFkCompany();
    }

    protected function denormalizeToResource(CompanyTransfer $companyTransfer): CompaniesStorefrontResource
    {
        return $this->serializer->denormalize(
            $this->companyResourceMapper->mapCompanyTransferToResourceData($companyTransfer),
            CompaniesStorefrontResource::class,
        );
    }
}
