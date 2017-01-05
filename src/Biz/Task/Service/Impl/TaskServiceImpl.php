<?php

namespace Biz\Task\Service\Impl;

use Biz\BaseService;
use Biz\Course\Service\CourseService;
use Biz\Task\Dao\TaskDao;
use Topxia\Common\ArrayToolkit;
use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\StrategyContext;
use Biz\Task\Service\TaskResultService;
use Codeages\Biz\Framework\Event\Event;
use Biz\Activity\Service\ActivityService;

class TaskServiceImpl extends BaseService implements TaskService
{
    public function getTask($id)
    {
        return $this->getTaskDao()->get($id);
    }

    public function createTask($fields)
    {
        $this->beginTransaction();
        try{
            $strategy = $this->createCourseStrategy($fields['fromCourseId']);
            $task = $strategy->createTask($fields);
            $this->dispatchEvent("course.task.create", new Event($task));
            $this->commit();
            return $task;
        }catch (\Exception $exception){
            $this->rollback();
            throw $exception;
        }
    }

    public function updateTask($id, $fields)
    {
        $this->beginTransaction();
        try{
            $task     = $this->getTask($id);
            $strategy = $this->createCourseStrategy($task['courseId']);
            $task     = $strategy->updateTask($id, $fields);
            $this->commit();
            return $task;
        } catch (\Exception $exception){
            $this->rollback();
            throw $exception;
        }
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
            'categoryId',
            'number'
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

    public function findTasksByCourseIds($courseIds)
    {
        return $this->getTaskDao()->findByCourseIds($courseIds);
    }

    public function countTasksByCourseId($courseId)
    {
        return $this->getTaskDao()->count(array('courseId' => $courseId));
    }

    public function findTasksByIds(array $ids)
    {
        return $this->getTaskDao()->findByIds($ids);
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

    public function findTasksFetchActivityAndResultByCourseId($courseId)
    {
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
        //设置任务是否解锁
        foreach ($tasks as &$task) {
            $task['lock'] = !(empty($task['result']) && empty($task['isOptional']) && $task['type'] != 'live');
        }
        return $tasks;
    }

    public function findUserTeachCoursesTasksByCourseSetId($userId, $courseSetId)
    {
        $conditions     = array(
            'userId' => $userId
        );
        $myTeachCourses = $this->getCourseService()->findUserTeachCourses($conditions, 0, PHP_INT_MAX, true);

        $conditions = array(
            'courseIds'   => ArrayToolkit::column($myTeachCourses, 'courseId'),
            'courseSetId' => $courseSetId
        );
        $courses    = $this->getCourseService()->searchCourses($conditions, array('createdTime' => 'DESC'), 0, PHP_INT_MAX);

        return $this->findTasksByCourseIds(ArrayToolkit::column($courses, 'id'));
    }

    public function search($conditions, $orderBy, $start, $limit)
    {
        return $this->getTaskDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function count($conditions)
    {
        return $this->getTaskDao()->count($conditions);
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
            'activityId'   => $task['activityId'],
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
            throw $this->createAccessDeniedException("task #{taskId} can not do. ");
        }

        $this->getTaskResultService()->waveLearnTime($taskResult['id'], $time);
    }

    public function finishTask($taskId)
    {
        $task = $this->tryTakeTask($taskId);

        if (!$this->isFinished($taskId)) {
            throw $this->createAccessDeniedException("can not finish task #{$taskId}.");
        }

        return $this->finishTaskResult($taskId);
    }

    public function finishTaskResult($taskId)
    {
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);

        if (empty($taskResult)) {
            throw $this->createAccessDeniedException('task access denied. ');
        }

        if ($taskResult['status'] === 'finish') {
            return $taskResult;
        }

        $update['updatedTime']  = time();
        $update['status']       = 'finish';
        $update['finishedTime'] = time();
        $taskResult             = $this->getTaskResultService()->updateTaskResult($taskResult['id'], $update);
        return $taskResult;
    }

    public function isFinished($taskId)
    {
        $task   = $this->getTask($taskId);
        $course = $this->getCourseService()->getCourse($task['courseId']);
        // TODO
        return true;
        return $course[''] && $this->getActivityService()->isFinished($task['activityId']);
    }

    public function tryTakeTask($taskId)
    {
        if (!$this->canLearnTask($taskId)) {
             throw $this->createAccessDeniedException("the Task is Locked");
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
        $task = $this->getTask($taskId);
        list($course, ) = $this->getCourseService()->tryTakeCourse($task['courseId']);

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

    public function findTasksFetchActivityByChapterId($chapterId)
    {
        $tasks = $this->findTasksByChapterId($chapterId);

        $activityIds = ArrayToolkit::column($tasks, 'activityId');
        $activities  = $this->getActivityService()->findActivities($activityIds);
        $activities  = ArrayToolkit::index($activities, 'id');

        array_walk($tasks, function (&$task) use ($activities) {
            $activity         = $activities[$task['activityId']];
            $task['activity'] = $activity;
        });
        return $tasks;
    }

    public function findToLearnTasksByCourseId($courseId)
    {
        list($course,) = $this->getCourseService()->tryTakeCourse($courseId);
        $toLearnTasks = array();
        if ($course['learnMode'] == 'freeMode') {
            $toLearnTasks[] = $this->getToLearnTaskWithFreeMode($courseId);
        } elseif ($course['learnMode'] == 'lockMode') {
            $toLearnTasks = $this->getToLearnTasksWithLockMode($courseId);
        } else {
            return $toLearnTasks;
        }

        $activityIds = ArrayToolkit::column($toLearnTasks, 'activityId');

        $activities = $this->getActivityService()->findActivities($activityIds);
        $activities = ArrayToolkit::index($activities, 'id');

        $taskIds     = ArrayToolkit::column($toLearnTasks, 'id');
        $taskResults = $this->getTaskResultService()->findUserTaskResultsByTaskIds($taskIds);

        array_walk($toLearnTasks, function (&$task) use ($activities, $taskResults) {
            $task['activity'] = $activities[$task['activityId']];
            foreach ($taskResults as $key => $result) {
                if ($result['courseTaskId'] != $task['id']) {
                    continue;
                }
                $task['result'] = $result;
            }
        });
        //设置任务是否解锁
        foreach ($toLearnTasks as &$task) {
            $task['lock'] = !(empty($task['result']) && empty($task['isOptional']) && $task['type'] != 'live');
        }
        return $toLearnTasks;
    }

    /**
     *
     * 自由式
     * 1.获取所有的在学中的任务结果，如果为空，则学员学员未开始学习或者已经学完，取第一个任务作为下一个学习任务，
     * 2.如果不为空，则按照任务序列返回第一个作为下一个学习任务
     * 任务式
     * 1.获取所有的在学中的任务结果，如果为空，则学员学员未开始学习或者已经学完，取第前三个作为任务，
     * 2.如果不为空，则取关联的三个。
     *
     * 自由式和任务式的逻辑由任务策略完成
     * @param $courseId
     * @return array tasks
     */


    protected function getToLearnTaskWithFreeMode($courseId)
    {
        $taskResults = $this->getTaskResultService()->findUserProgressingTaskResultByCourseId($courseId);
        if (empty($taskResults)) {
            $minSeq      = $this->getTaskDao()->getMinSeqByCourseId($courseId);
            $toLearnTask = $this->getTaskDao()->getByCourseIdAndSeq($courseId, $minSeq);
        } else {
            $latestTaskResult = array_shift($taskResults);
            $latestLearnTask  = $this->getTask($latestTaskResult['courseTaskId']); //获取最新学习未学完的课程
            $tasks            = $this->getTaskDao()->search(array('seq_GE' => $latestLearnTask['seq'], 'courseId' => $courseId), array('seq' => 'ASC'), 0, 2);
            $toLearnTask      = array_pop($tasks);//如果当正在学习的是最后一个，则取当前在学的任务
        }
        return $toLearnTask;
    }


    protected function getToLearnTasksWithLockMode($courseId)
    {
        $toLearnTaskCount = 3;
        $taskResult       = $this->getTaskResultService()->getUserLatestFinishedTaskResultByCourseId($courseId);
        $toLearnTasks     = array();
        if (empty($taskResult)) {
            $toLearnTasks = $this->getTaskDao()->search(array('courseId' => $courseId), array('seq' => 'ASC'), 0, $toLearnTaskCount);
            return $toLearnTasks;
        }

        //取出所有的任务
        $taskCount = $this->countTasksByCourseId($courseId);
        $tasks     = $this->getTaskDao()->search(array('courseId' => $courseId), array('seq' => 'ASC'), 0, $taskCount);

        if (count($tasks) <= $toLearnTaskCount) {
            $toLearnTasks = $tasks;
            return $toLearnTasks;
        }

        $previousTask = null;
        //向后取待学习的三个任务
        foreach ($tasks as $task) {
            if ($task['id'] == $taskResult['courseTaskId']) {
                $toLearnTasks[] = $task;
                $previousTask   = $task;
            }
            if ($previousTask && $task['seq'] > $previousTask['seq'] and count($toLearnTasks) < $toLearnTaskCount) {
                array_push($toLearnTasks, $task);
                $previousTask = $task;
            }
        }

        //向后去待学习的任务不足3个，向前取。
        $reverseTasks = array_reverse($tasks);
        if (count($toLearnTasks) < $toLearnTaskCount) {
            foreach ($reverseTasks as $task) {
                if ($task['id'] == $taskResult['courseTaskId']) {
                    $previousTask = $task;
                }
                if ($previousTask && $task['seq'] < $previousTask['seq'] and count($toLearnTasks) < $toLearnTaskCount) {
                    array_unshift($toLearnTasks, $task);
                    $previousTask = $task;
                }
            }
        }
        return $toLearnTasks;

    }


    public function trigger($id, $eventName, $data = array())
    {
        $task = $this->getTask($id);
        $this->getActivityService()->trigger($task['activityId'], $eventName, $data);
        return $this->getTaskResultService()->getUserTaskResultByTaskId($id);
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

    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }
}
