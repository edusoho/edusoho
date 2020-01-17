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
                'id > :id_GT',
                'user_id = :user_id',
                'user_id IN (:user_ids)',
                'operate_type = :operate_type',
                'operate_time > :operate_time_GT',
                'operate_time >= :operate_time_GE',
                'operate_time < :operate_time_LT',
                'target_type = :target_type',
                'member_id != :exclude_member_id',
                'member_id = :member_id',
                'target_id = :target_id',
                'parent_id > :parent_id_GT',
                'parent_id = :parent_id',
                'join_course_set = :join_course_set',
                'exit_course_set = :exit_course_set',
                'reason_type != :exclude_reason_type',
                'created_time >= :created_time_GE',
                'created_time <= :created_time_LE',
                'created_time < :created_time_LT',
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

    public function countGroupByUserId($field, $conditions)
    {
        if (!in_array($field, array('target_id', 'course_set_id'))) {
            return array();
        }

        $builder = $this->createQueryBuilder($conditions)
            ->select("count(distinct({$field})) as count, user_id")
            ->groupBy('user_id');

        return $builder->execute()->fetchAll();
    }

    public function countGroupByDate($conditions, $sort, $dateColumn = 'operate_time')
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select("count(id) as count ,from_unixtime({$dateColumn},'%Y-%m-%d') date")
            ->groupBy('date')
            ->orderBy('date', $sort);

        return $builder->execute()->fetchAll(0) ?: array();
    }
}
