<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CompaniesRestApi\Api\Storefront\Relationship;

use Generated\Api\Storefront\CompaniesStorefrontResource;
use Generated\Shared\Transfer\CompanyTransfer;
use Spryker\ApiPlatform\Relationship\PerItemRelationshipResolverInterface;
use Spryker\Glue\CompaniesRestApi\Api\Storefront\Mapper\CompanyResourceMapperInterface;
use Spryker\Glue\CompaniesRestApi\Api\Storefront\Reader\CompanyReaderInterface;
use Spryker\Service\Serializer\SerializerServiceInterface;

class CompaniesRelationshipResolver implements PerItemRelationshipResolverInterface
{
    public function __construct(
        protected CompanyReaderInterface $companyReader,
        protected CompanyResourceMapperInterface $companyResourceMapper,
        protected SerializerServiceInterface $serializer,
    ) {
    }

    /**
     * @param array<object> $parentResources
     * @param array<string, mixed> $context
     *
     * @return array<object>
     */
    public function resolve(array $parentResources, array $context): array
    {
        $allResources = [];

        foreach ($this->resolvePerItem($parentResources, $context) as $resources) {
            $allResources = array_merge($allResources, $resources);
        }

        return $allResources;
    }

    /**
     * @param array<object> $parentResources
     * @param array<string, mixed> $context
     *
     * @return array<string, array<object>>
     */
    public function resolvePerItem(array $parentResources, array $context): array
    {
        $companyIdsIndexedByParentResourceUuid = [];

        foreach ($parentResources as $parentResource) {
            $uuid = $parentResource->uuid ?? null;

            if ($uuid === null) {
                continue;
            }

            $companyIdsIndexedByParentResourceUuid[$uuid] = $parentResource->idCompany ?? null;
        }

        $idCompanies = array_values(array_filter($companyIdsIndexedByParentResourceUuid, fn ($id) => $id !== null));
        $companiesIndexedByCompanyId = $this->companyReader->getCompaniesIndexedByCompanyId($idCompanies);

        $result = [];

        foreach ($companyIdsIndexedByParentResourceUuid as $uuid => $idCompany) {
            $result[$uuid] = [];

            if ($idCompany === null) {
                continue;
            }

            $companyTransfer = $companiesIndexedByCompanyId[$idCompany] ?? null;

            if ($companyTransfer === null) {
                continue;
            }

            $result[$uuid] = [$this->denormalizeToCompanyResource($companyTransfer)];
        }

        return $result;
    }

    protected function denormalizeToCompanyResource(CompanyTransfer $companyTransfer): CompaniesStorefrontResource
    {
        return $this->serializer->denormalize(
            $this->companyResourceMapper->mapCompanyTransferToResourceData($companyTransfer),
            CompaniesStorefrontResource::class,
        );
    }
}
