<?php

namespace Biz\Order\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OrderLogDao extends  GeneralDaoInterface
{
	public function findByOrderId($orderId);
}