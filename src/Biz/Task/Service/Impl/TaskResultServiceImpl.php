<?php


namespace Biz\Task\Service\Impl;


use Biz\BaseService;
use Biz\Task\Dao\TaskResultDao;
use Biz\Task\Service\TaskResultService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Exception\AccessDeniedException;

class TaskResultServiceImpl extends BaseService implements TaskResultService
{
    public function findUserTaskResultsByCourseId($courseId)
    {
        $user = $this->getCurrentUser();

        if(!$user->isLogin()){
            throw new AccessDeniedException('can not get task results because user not login');
        }

        return $this->getTaskResultDao()->findByCourseIdAndUserId($courseId, $user['id']);
    }

    public function getUserTaskResultByTaskId($taskId)
    {
        $user = $this->getCurrentUser();

        if(!$user->isLogin()){
            throw new AccessDeniedException('can not get task result because user not login');
        }

        return $this->getTaskResultDao()->getByTaskIdAndUserId($taskId, $user['id']);
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

    public function findUserProgressingTaskResultByActivityId($activityId)
    {
        $user = $this->getCurrentUser();

        if(!$user->isLogin()){
            throw new AccessDeniedException();
        }

        $conditions = array(
            'activityId' => $activityId,
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