<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CompaniesRestApi\Api\Storefront\Reader;

use Generated\Shared\Transfer\CompanyCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Spryker\Client\Company\CompanyClientInterface;

class CompanyReader implements CompanyReaderInterface
{
    public function __construct(protected CompanyClientInterface $companyClient)
    {
    }

    public function findCompanyByUuid(string $uuid): ?CompanyTransfer
    {
        $companyResponseTransfer = $this->companyClient->findCompanyByUuid(
            (new CompanyTransfer())->setUuid($uuid),
        );

        if (!$companyResponseTransfer->getIsSuccessful()) {
            return null;
        }

        return $companyResponseTransfer->getCompanyTransfer();
    }

    public function findCompanyByIdCompany(int $idCompany): ?CompanyTransfer
    {
        $companyTransfer = $this->companyClient->getCompanyById(
            (new CompanyTransfer())->setIdCompany($idCompany),
        );

        if (!$companyTransfer->getUuid()) {
            return null;
        }

        return $companyTransfer;
    }

    /**
     * @param array<int> $idCompanies
     *
     * @return array<int, \Generated\Shared\Transfer\CompanyTransfer>
     */
    public function getCompaniesIndexedByCompanyId(array $idCompanies): array
    {
        $uniqueIdCompanies = array_values(array_unique($idCompanies));

        if ($uniqueIdCompanies === []) {
            return [];
        }

        $companyCriteriaFilterTransfer = (new CompanyCriteriaFilterTransfer())
            ->setCompanyIds($uniqueIdCompanies);

        $companyCollectionTransfer = $this->companyClient->getCompanyCollection($companyCriteriaFilterTransfer);

        $companies = [];

        foreach ($companyCollectionTransfer->getCompanies() as $companyTransfer) {
            $idCompany = $companyTransfer->getIdCompany();

            if ($idCompany === null) {
                continue;
            }

            $companies[$idCompany] = $companyTransfer;
        }

        return $companies;
    }
}
