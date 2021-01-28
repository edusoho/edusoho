<?php

namespace Codeages\Biz\Invoice\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface InvoiceDao extends GeneralDaoInterface
{
    public function getBySn($sn);
}
