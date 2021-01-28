<?php

namespace Biz\Marketing\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface MarketingOrderDao extends GeneralDaoInterface
{
    /**
     * 根据微营销订单Id 获取订单数据
     *
     * @param array $marketingOrderId
     *
     * @return array|null
     */
    public function getOrderByMarketingOrderId($marketingOrderId);
}
