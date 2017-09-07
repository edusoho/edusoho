<?php

namespace Biz\MemberOperation\Dao\Impl;

use Biz\MemberOperation\Dao\MemberOperationRecordDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MemberOperationRecordDaoImpl extends GeneralDaoImpl implements MemberOperationRecordDao
{
    protected $table = 'member_operation_record';

    public function declares()
    {
        return array(
            'timestamps' => array('created_time'),
            'serializes' => array(
                'data' => 'json',
            ),
            'orderbys' => array('id', 'created_time', 'operate_time'),
            'conditions' => array(
                'id = :id',
                'operate_type = :operate_type',
                'operate_time > :operate_time_GT',
                'operate_time < :operate_time_LT',
                'target_type = :target_type',
            ),
        );
    }

    public function countGroupByDate($conditions, $sort, $dateColumn = 'operate_time')
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select("count(id) as count ,from_unixtime({$dateColumn},'%Y-%m-%d') date")
            ->groupBy("date {$sort}");

        return $builder->execute()->fetchAll(0) ?: array();
    }
}
