<?php


namespace Biz\Task\Service\Impl;


use Biz\BaseService;
use Biz\Task\Dao\TaskResultDao;
use Biz\Task\Service\TaskResultService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Exception\AccessDeniedException;

class TaskResultServiceImpl extends BaseService implements TaskResultService
{
    public function findTaskResultsByCourseId($courseId, $userId)
    {
        return $this->getTaskResultDao()->findByCourseId($courseId, $userId);
    }

    public function getTaskResultByTaskIdAndUserId($taskId, $userId)
    {
        return $this->getTaskResultDao()->getByTaskIdAndUserId($taskId, $userId);
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

        if(!$user->isLogin()){
            throw new AccessDeniedException('user must be login');
        }

        $taskResult['createdTime'] = time();
        $taskResult['status'] = 'start';

        $this->getTaskResultDao()->create($taskResult);
    }

    public function updateTaskResult($id, $taskResult)
    {
        return $this->getTaskResultDao()->update($id, $taskResult);
    }


    public function getTaskResultByTaskIdAndActivityId($taskId, $activityId)
    {
        return $this->getTaskResultDao()->getByTaskIdAndActivityId($taskId, $activityId);
    }

    public function findUserProgressingTaskByCourseIdAndActivityId($courseId, $activityId)
    {
        $user = $this->getCurrentUser();

        if(!$user->isLogin()){
            throw new AccessDeniedException();
        }

        $conditions = array(
            'activityId' => $activityId,
            'courseId'   => $courseId,
            'userId' => $user['id'],
            'status' => 'start'
        );

        $count = $this->getTaskResultDao()->count($conditions);

        return $this->getTaskResultDao()->search($conditions, array('createdTime' => 'DESC'), 0, $count);
    }

    /**
     * @return TaskResultDao
     */
    protected function getTaskResultDao()
    {
        return $this->createDao('Task:TaskResultDao');
    }
}