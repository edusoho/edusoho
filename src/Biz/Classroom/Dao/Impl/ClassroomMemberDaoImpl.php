<?php


namespace Biz\Classroom\Dao\Impl;


use Biz\Classroom\Dao\ClassroomMemberDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ClassroomMemberDaoImpl extends GeneralDaoImpl implements ClassroomMemberDao
{
    protected $table = 'classroom_member';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'serializes' => array('assistantIds' => 'json', 'teacherIds' => 'json', 'service' => 'json'),
            'orderbys'   => array('name', 'createdTime'),
            'conditions' => array(
                'userId = :userId',
                'classroomId = :classroomId',
                'noteNum > :noteNumGreaterThan',
                'role LIKE :role',
                'role IN (:roles)',
                'userId IN ( :userIds)',
                'createdTime >= :startTimeGreaterThan',
                'createdTime >= :createdTime_GE',
                'createdTime < :startTimeLessThan'
            )
        );
    }

    public function findByUserIdAndClassroomIds($userId, $classroomIds)
    {
        if (empty($classroomIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($classroomIds) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE userId = {$userId} AND classroomId IN ({$marks});";

        return $this->db()->fetchAll($sql, $classroomIds) ?: array();
    }

}