<?php

namespace Biz\RefererLog\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OrderRefererDao extends GeneralDaoInterface
{
    public function getByUv($uv);

    public function getLikeByOrderId($orderId);
}
