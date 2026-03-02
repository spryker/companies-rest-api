<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CompaniesRestApi\Processor\Company\RestResponseBuilder;

use Generated\Shared\Transfer\RestCompanyAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface CompanyRestResponseBuilderInterface
{
    public function createCompanyRestResponse(
        string $companyUuid,
        RestCompanyAttributesTransfer $restCompanyAttributesTransfer
    ): RestResponseInterface;

    public function createCompanyRestResource(
        string $companyUuid,
        RestCompanyAttributesTransfer $restCompanyAttributesTransfer
    ): RestResourceInterface;

    public function createCompanyNotFoundError(): RestResponseInterface;

    public function createResourceNotImplementedError(): RestResponseInterface;

    public function createCompanyUserNotSelectedError(): RestResponseInterface;
}
