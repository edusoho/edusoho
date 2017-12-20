<?php

namespace Biz\MemberOperation\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface MemberOperationRecordDao extends GeneralDaoInterface
{
    public function countGroupByDate($conditions, $sort, $dateColumn = 'operate_time');

    public function getRecordByOrderIdAndType($orderId, $type);

    public function countUserIdsByConditions($conditions);

    public function countGroupByUserId($field, $conditions);
}
