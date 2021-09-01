<?php


namespace Biz\OperationStatistic\Dao\Impl;


use Biz\OperationStatistic\Dao\OperationDetailStatisticDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class OperationDetailStatisticDaoImpl extends AdvancedDaoImpl implements OperationDetailStatisticDao
{
    protected $table = 'operation_detail_statistic';

    public function declares()
    {
        return [
            'timestamps' => ['created_time'],
            'orderbys' => ['created_time'],
            'conditions' => [
                'id = :id',
                'id IN (:ids)',
                'version = :version',
                'operator_id = :operatorId',
                'target_id = :targetId',
                'target_type = :type',
            ],
            'serializes' => [
                'data' => 'json',
            ],
        ];
    }
}