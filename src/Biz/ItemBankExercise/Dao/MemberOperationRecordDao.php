<?php

namespace Biz\ItemBankExercise\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface MemberOperationRecordDao extends AdvancedDaoInterface
{
    public function deleteByExerciseId($exerciseId);

    public function findRecordsByOrderIdAndType($orderId, $type);
}
