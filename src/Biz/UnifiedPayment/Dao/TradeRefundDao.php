<?php

namespace Biz\UnifiedPayment\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TradeRefundDao extends GeneralDaoInterface
{
    public function findByTradeSn($sn);
}
