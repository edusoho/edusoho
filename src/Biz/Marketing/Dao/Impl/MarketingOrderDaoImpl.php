<?php

namespace Biz\Marketing\Dao\Impl;

use Biz\Marketing\Dao\MarketingOrderDao;
use Codeages\Biz\Order\Dao\Impl\OrderDaoImpl;

class MarketingOrderDaoImpl extends OrderDaoImpl implements MarketingOrderDao
{
    public function getOrderByMarketingOrderId($marketingOrderId)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `source` = 'marketing' AND `create_extra` LIKE ? order by `updated_time` DESC LIMIT 1 ";

        return $this->db()->fetchAssoc($sql, array('%marketingOrderId":"'.$marketingOrderId.'"%')) ?: array();
    }
}
