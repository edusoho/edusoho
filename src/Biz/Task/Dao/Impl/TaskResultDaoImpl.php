<?php

namespace Biz\Task\Dao\Impl;

use Biz\Task\Dao\TaskResultDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TaskResultDaoImpl extends GeneralDaoImpl implements TaskResultDao
{
    protected $table = 'course_task_result';

    public function findByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? and userId = ? ";
        return $this->db()->fetchAll($sql, array($courseId, $userId)) ?: array();
    }

    public function getByTaskIdAndUserId($taskId, $userId)
    {
        return $this->getByFields(array(
            'courseTaskId' => $taskId,
            'userId'       => $userId
        ));
    }

    public function findByActivityIdAndUserId($activityId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE activityId = ? and userId = ? ";
        return $this->db()->fetchAll($sql, array($activityId, $userId)) ?: array();
    }

    public function declares()
    {
        return array(
            'orderbys'   => array('createdTime'),
            'timestamps' => array('createdTime', 'updatedTime'),
            'conditions' => array(
                'status =:status',
                'userId =:userId',
                'activityId =:activityId'
            )
        );
    }
}
