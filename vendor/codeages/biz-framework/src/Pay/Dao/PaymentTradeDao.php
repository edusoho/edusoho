<?php

namespace Codeages\Biz\Framework\Pay\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface PaymentTradeDao extends GeneralDaoInterface
{
    public function getByOrderSnAndPlatform($orderSn, $platform);

    public function getByTradeSn($sn);
}