<?php

namespace Codeages\Biz\Invoice\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface InvoiceTemplateDao extends GeneralDaoInterface
{
    public function getDefaultByUserId($userId);
}