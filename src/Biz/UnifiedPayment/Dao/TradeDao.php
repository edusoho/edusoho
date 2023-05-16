<?php

namespace Biz\UnifiedPayment\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TradeDao extends GeneralDaoInterface
{
    public function getByOrderSnAndPlatform($orderSn, $platform);

    public function getById($id);

    public function getByTradeSn($sn);

    public function findByIds($ids);

    public function findByTradeSns($sns);

    public function findByOrderSns($orderSns);

    public function findByOrderSn($orderSn);

    public function getByPlatformSn($platformSn);
}
