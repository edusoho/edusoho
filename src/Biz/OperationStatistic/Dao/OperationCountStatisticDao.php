<?php

namespace Biz\OperationStatistic\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface OperationCountStatisticDao extends AdvancedDaoInterface
{
    public function getByTargetTypeAndOperatorId($targetType, $operatorId);
}
