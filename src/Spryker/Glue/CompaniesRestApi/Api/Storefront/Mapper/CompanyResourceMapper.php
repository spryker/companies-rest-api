<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CompaniesRestApi\Api\Storefront\Mapper;

use Generated\Shared\Transfer\CompanyTransfer;

class CompanyResourceMapper implements CompanyResourceMapperInterface
{
    /**
     * @return array<string, mixed>
     */
    public function mapCompanyTransferToResourceData(CompanyTransfer $companyTransfer): array
    {
        return [
            'uuid' => $companyTransfer->getUuid(),
            'name' => $companyTransfer->getName(),
            'isActive' => $companyTransfer->getIsActive(),
            'status' => $companyTransfer->getStatus(),
        ];
    }
}
