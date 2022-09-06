<?php

namespace Biz\MemberOperation\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface MemberOperationRecordDao extends AdvancedDaoInterface
{
    public function countGroupByDate($conditions, $sort, $dateColumn = 'operate_time');

    public function getRecordByOrderIdAndType($orderId, $type);

    public function countUserIdsByConditions($conditions);

    public function countGroupByUserId($field, $conditions);
}
