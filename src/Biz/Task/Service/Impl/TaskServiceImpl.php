<?php

namespace Biz\Task\Service\Impl;

use Biz\BaseService;
use Biz\Task\Dao\TaskDao;
use Topxia\Common\ArrayToolkit;
use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\StrategyContext;
use Biz\Task\Service\TaskResultService;
use Codeages\Biz\Framework\Event\Event;
use Topxia\Service\Course\CourseService;
use Biz\Activity\Service\ActivityService;

class TaskServiceImpl extends BaseService implements TaskService
{
    public function getTask($id)
    {
        return $this->getTaskDao()->get($id);
    }

    public function createTask($fields)
    {
        $strategy = $this->createCourseStrategy($fields['fromCourseId']);

        $task = $strategy->createTask($fields);

        $this->biz['dispatcher']->dispatch("course.task.create", new Event($task));
        return $task;
    }

    public function updateTask($id, $fields)
    {
        $strategy = $this->createCourseStrategy($fields['fromCourseId']);

        $task = $strategy->updateTask($id, $fields);
        return $task;
    }

    public function publishTask($id)
    {
        $task     = $this->getTask($id);
        $strategy = $this->createCourseStrategy($task['courseId']);

        $task = $strategy->publishTask($task);
        return $task;
    }

    public function unpublishTask($id)
    {
        $task     = $this->getTask($id);
        $strategy = $this->createCourseStrategy($task['courseId']);

        $task = $strategy->unpublishTask($task);
        return $task;
    }

    public function updateSeq($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, array(
            'seq',
            'categoryId'
        ));
        return $this->getTaskDao()->update($id, $fields);
    }

    public function deleteTask($id)
    {
        $task = $this->getTask($id);

        if (!$this->getCourseService()->tryManageCourse($task['courseId'])) {
            throw $this->createAccessDeniedException('无权删除任务');
        }
        $result = $this->createCourseStrategy($task['courseId'])->deleteTask($task);
        $this->biz['dispatcher']->dispatch("course.task.delete", new Event($task));
        return $result;
    }

    public function findTasksByCourseId($courseId)
    {
        return $this->getTaskDao()->findByCourseId($courseId);
    }

    public function countTasksByCourseId($courseId)
    {
        return $this->getTaskDao()->count(array('courseId' => $courseId));
    }

    public function findTasksFetchActivityByCourseId($courseId)
    {
        $tasks       = $this->findTasksByCourseId($courseId);
        $activityIds = ArrayToolkit::column($tasks, 'activityId');
        $activities  = $this->getActivityService()->findActivities($activityIds);
        $activities  = ArrayToolkit::index($activities, 'id');

        array_walk($tasks, function (&$task) use ($activities) {
            $activity         = $activities[$task['activityId']];
            $task['activity'] = $activity;
        });

        return $tasks;
    }

    public function findUserTasksFetchActivityAndResultByCourseId($courseId)
    {
        $user = $this->getCurrentUser();
        if (!$this->getCourseService()->isCourseStudent($courseId, $user->getId())) {
            return array();
        }

        $tasks = $this->findTasksFetchActivityByCourseId($courseId);
        if (empty($tasks)) {
            return array();
        }

        $taskResults = $this->getTaskResultService()->findUserTaskResultsByCourseId($courseId);
        $taskResults = ArrayToolkit::index($taskResults, 'courseTaskId');

        array_walk($tasks, function (&$task) use ($taskResults) {
            foreach ($taskResults as $key => $result) {
                if ($key != $task['id']) {
                    continue;
                }
                $task['result'] = $result;
            }
        });
        return $tasks;
    }

    public function startTask($taskId)
    {
        $task = $this->getTask($taskId);

        $user = $this->getCurrentUser();

        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);

        if (!empty($taskResult)) {
            return;
        }

        $taskResult = array(
            'activityId'   => $task['id'],
            'courseId'     => $task['courseId'],
            'courseTaskId' => $task['id'],
            'userId'       => $user['id']
        );

        $this->getTaskResultService()->createTaskResult($taskResult);
    }

    public function doTask($taskId, $time = TaskService::LEARN_TIME_STEP)
    {
        $task = $this->tryTakeTask($taskId);

        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);

        if (empty($taskResult)) {
            throw new AccessDeniedException('任务不在进行状态');
        }

        $this->getTaskResultService()->waveLearnTime($taskResult['id'], $time);
    }

    public function finishTask($taskId)
    {
        $task = $this->tryTakeTask($taskId);

        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);

        if (empty($taskResult)) {
            throw $this->createAccessDeniedException('该任务不在进行状态');
        }

        if ($taskResult['status'] === 'finish') {
            return;
        }

        $update['updatedTime']  = time();
        $update['status']       = 'finish';
        $update['finishedTime'] = time();
        $this->getTaskResultService()->updateTaskResult($taskResult['id'], $update);
    }

    public function tryTakeTask($taskId)
    {
        if (!$this->canLearnTask($taskId)) {
            // throw $this->createAccessDeniedException("the Task is Locked");
        }
        $task = $this->getTask($taskId);

        if (empty($task)) {
            throw $this->createNotFoundException("task does not exist");
        }
        return $task;
    }

    public function getNextTask($taskId)
    {
        $task = $this->getTask($taskId);

        // if the task is last task, no next test can be return
        $nextTask = $this->getTaskDao()->getNextTaskByCourseIdAndSeq($task['courseId'], $task['seq']);
        if (empty($nextTask)) {
            return array();
        }

        if (!$this->canLearnTask($taskId)) {
            return array();
        }

        //if the task is first, when get next task, we need to know if the task if finish, if not  return null;
        $firstTask = $this->getTaskDao()->getPreTaskByCourseIdAndSeq($task['courseId'], $task['seq']);
        if (!empty($firstTask)) {
            $isTaskLearned = $this->isTaskLearned($taskId);
            if (!$isTaskLearned) {
                return array();
            }
        }
        return $nextTask;
    }

    public function canLearnTask($taskId)
    {
        $task                  = $this->getTask($taskId);
        list($course, $member) = $this->getCourseService()->tryTakeCourse($task['courseId']);

        $canLearnTask = $this->createCourseStrategy($course['id'])->canLearnTask($task);
        return $canLearnTask;
    }

    public function isTaskLearned($taskId)
    {
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);

        return empty($taskResult) ? false : ('finish' == $taskResult['status']);
    }

    public function getMaxSeqByCourseId($courseId)
    {
        return $this->getTaskDao()->getMaxSeqByCourseId($courseId);
    }

    public function findTasksByChapterId($chapterId)
    {
        return $this->getTaskDao()->findByChapterId($chapterId);
    }

    /**
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }

    protected function createCourseStrategy($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course)) {
            throw $this->createNotFoundException('course does not exist');
        }

        return StrategyContext::getInstance()->createStrategy($course['isDefault'], $this->biz);
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->biz->service('Task:TaskResultService');
    }
}
