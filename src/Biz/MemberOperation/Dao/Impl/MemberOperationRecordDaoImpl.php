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
                'user_id IN (:user_ids)',
                'operate_type = :operate_type',
                'operate_time > :operate_time_GT',
                'operate_time >= :operate_time_GE',
                'operate_time < :operate_time_LT',
                'target_type = :target_type',
                'member_id != :exclude_member_id',
                'member_id = :member_id',
                'target_id = :target_id',
            ),
        );
    }

    public function getRecordByOrderIdAndType($orderId, $type)
    {
        return $this->getByFields(array('order_id' => $orderId, 'operate_type' => $type));
    }

    public function countUserIdsByConditions($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('count(distinct(user_id))');

        return $builder->execute()->fetchColumn(0) ?: 0;
    }

    public function countGroupByDate($conditions, $sort, $dateColumn = 'operate_time')
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select("count(id) as count ,from_unixtime({$dateColumn},'%Y-%m-%d') date")
            ->groupBy("date {$sort}");

        return $builder->execute()->fetchAll(0) ?: array();
    }
}
