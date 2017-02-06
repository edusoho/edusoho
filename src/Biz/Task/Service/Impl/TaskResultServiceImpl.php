<?php

namespace Biz\Task\Service\Impl;

use Biz\BaseService;
use Biz\Task\Dao\TaskResultDao;
use Topxia\Common\ArrayToolkit;
use Biz\Task\Service\TaskResultService;

class TaskResultServiceImpl extends BaseService implements TaskResultService
{
    public function analysisCompletedTaskDataByTime($startTime, $endTime)
    {
        return $this->getTaskResultDao()->analysisCompletedTaskDataByTime($startTime, $endTime);
    }

    public function findUserTaskResultsByCourseId($courseId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('can not get task results because user not login');
        }

        return $this->getTaskResultDao()->findByCourseIdAndUserId($courseId, $user['id']);
    }

    public function getUserTaskResultByTaskId($taskId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('can not get task result because user not login');
        }

        return $this->getTaskResultDao()->getByTaskIdAndUserId($taskId, $user['id']);
    }

    public function deleteUserTaskResultByTaskId($taskId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('can not get task result because user not login');
        }

        return $this->getTaskResultDao()->deleteByTaskIdAndUserId($taskId, $user['id']);
    }

    public function createTaskResult($taskResult)
    {
        ArrayToolkit::requireds($taskResult, array(
            'activityId',
            'courseId',
            'courseTaskId',
            'userId'
        ));

        $user = $this->biz['user'];

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('user must be login');
        }

        $taskResult['status'] = 'start';

        $this->getTaskResultDao()->create($taskResult);
    }

    public function updateTaskResult($id, $taskResult)
    {
        return $this->getTaskResultDao()->update($id, $taskResult);
    }

    public function waveLearnTime($id, $time)
    {
        return $this->getTaskResultDao()->wave(array($id), array(
            'time' => $time
        ));
    }

    public function findUserProgressingTaskResultByActivityId($activityId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('unlogin');
        }

        $conditions = array(
            'activityId' => $activityId,
            'userId'     => $user['id'],
            'status'     => 'start'
        );

        $count = $this->getTaskResultDao()->count($conditions);
        return $this->getTaskResultDao()->search($conditions, array('createdTime' => 'DESC'), 0, $count);
    }

    public function findUserProgressingTaskResultByCourseId($courseId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('unlogin');
        }

        $conditions = array(
            'courseId' => $courseId,
            'userId'   => $user['id'],
            'status'   => 'start'
        );

        $count = $this->getTaskResultDao()->count($conditions);
        return $this->getTaskResultDao()->search($conditions, array('createdTime' => 'DESC'), 0, $count);
    }

    public function countTaskResults($conditions)
    {
        return $this->getTaskResultDao()->count($conditions);
    }

    public function getUserLatestFinishedTaskResultByCourseId($courseId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('unlogin');
        }
        $conditions = array(
            'userId'   => $user->getId(),
            'status'   => 'finish',
            'courseId' => $courseId
        );
        $taskResults = $this->getTaskResultDao()->search($conditions, array('updatedTime' => 'DESC'), 0, 1);
        $result      = array_shift($taskResults);
        return $result;
    }

    public function findUserTaskResultsByTaskIds($taskIds)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('unlogin');
        }
        if (empty($taskIds)) {
            return array();
        }
        return $this->getTaskResultDao()->findByTaskIdsAndUserId($taskIds, $user->getId());
    }

    public function countUsersByTaskIdAndLearnStatus($taskId, $status)
    {
        if ($status == 'all') {
            $status = null;
        }

        return $this->getTaskResultDao()->count(array('courseTaskId' => $taskId, 'status' => $status));
    }

    /**
     * 统计某个任务的学习次数，学习的定义为task_result的status为start、finish，不对用户去重；
     */
    public function countLearnNumByTaskId($taskId)
    {
        return $this->getTaskResultDao()->countLearnNumByTaskId($taskId);
    }

    public function searchTaskResults($conditions, $orderbys, $start, $limit)
    {
        return $this->getTaskResultDao()->search($conditions, $orderbys, $start, $limit);
    }

    public function findFinishedTasksByCourseIdGroupByUserId($courseId)
    {
        return $this->getTaskResultDao()->findFinishedTasksByCourseIdGroupByUserId($courseId);
    }

    public function findFinishedTimeByCourseIdGroupByUserId($courseId)
    {
        return $this->getTaskResultDao()->findFinishedTimeByCourseIdGroupByUserId($courseId);
    }

    public function sumLearnTimeByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getTaskResultDao()->sumLearnTimeByCourseIdAndUserId($courseId, $userId);
    }

    public function getLearnedTimeByCourseIdGroupByCourseTaskId($courseTaskId)
    {
        return $this->getTaskResultDao()->getLearnedTimeByCourseIdGroupByCourseTaskId($courseTaskId);
    }

    public function getWatchTimeByCourseIdGroupByCourseTaskId($courseTaskId)
    {
        return $this->getTaskResultDao()->getWatchTimeByCourseIdGroupByCourseTaskId($courseTaskId);
    }

    /**
     * @return TaskResultDao
     */
    protected function getTaskResultDao()
    {
        return $this->createDao('Task:TaskResultDao');
    }
}
