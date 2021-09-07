<?php

namespace Biz\OperationStatistic\Dao\Impl;

use Biz\OperationStatistic\Dao\OperationCountStatisticDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class OperationCountStatisticDaoImpl extends AdvancedDaoImpl implements OperationCountStatisticDao
{
    protected $table = 'operation_count_statistic';

    public function getByTargetTypeAndOperatorId($targetType, $operatorId)
    {
        return $this->getByFields(['target_type' => $targetType, 'operator_id' => $operatorId]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['created_time'],
            'orderbys' => ['created_time'],
            'conditions' => [
                'id = :id',
                'id IN (:ids)',
                'version = :version',
                'operator_id = :operator_id',
                'operator_num = :operation_num',
                'target_type = :target_type',
            ],
        ];
    }
}
