<?php

namespace Codeages\Biz\Framework\Order\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OrderDao extends GeneralDaoInterface
{
    public function getBySn($sn, array $options = array());

    public function findByIds(array $ids);

    public function countGroupByDate($startTime, $endTime, $status, $targetType);
}