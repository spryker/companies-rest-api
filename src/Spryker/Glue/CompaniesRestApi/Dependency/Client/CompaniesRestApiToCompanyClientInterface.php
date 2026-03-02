<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CompaniesRestApi\Dependency\Client;

use Generated\Shared\Transfer\CompanyResponseTransfer;
use Generated\Shared\Transfer\CompanyTransfer;

interface CompaniesRestApiToCompanyClientInterface
{
    public function getCompanyById(CompanyTransfer $companyTransfer): CompanyTransfer;

    public function findCompanyByUuid(CompanyTransfer $companyTransfer): CompanyResponseTransfer;
}
