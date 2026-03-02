<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CompaniesRestApi\Processor\Company\Mapper;

use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\RestCompanyAttributesTransfer;

class CompanyMapper implements CompanyMapperInterface
{
    public function mapCompanyTransferToRestCompanyAttributesTransfer(
        CompanyTransfer $companyTransfer,
        RestCompanyAttributesTransfer $restCompanyAttributesTransfer
    ): RestCompanyAttributesTransfer {
        return $restCompanyAttributesTransfer->fromArray($companyTransfer->toArray(), true);
    }
}
