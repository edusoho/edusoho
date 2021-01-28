<?php

namespace Codeages\Biz\Pay\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface PayTradeDao extends GeneralDaoInterface
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
