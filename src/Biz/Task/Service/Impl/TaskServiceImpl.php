<?php

namespace Biz\Task\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\Service\MemberService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskService;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Task\Strategy\CourseStrategy;
use Biz\Task\Service\TaskResultService;
use Biz\Task\TaskException;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Service\CourseSetService;
use Biz\Activity\Service\ActivityService;

class TaskServiceImpl extends BaseService implements TaskService
{
    /**
     * @var array
     *            包含序列化字段的学习类型，mediaType
     */
    private static $mediaList = array('video', 'audio', 'doc', 'ppt', 'flash');

    public function getTask($id)
    {
        return $this->getTaskDao()->get($id);
    }

    public function getCourseTask($courseId, $id)
    {
        $task = $this->getTaskDao()->get($id);
        if (empty($task) || $task['courseId'] != $courseId) {
            return array();
        }

        return $task;
    }

    public function getCourseTaskByCourseIdAndCopyId($courseId, $copyId)
    {
        $task = $this->getTaskDao()->getByCourseIdAndCopyId($courseId, $copyId);
        if (empty($task) || $task['courseId'] != $courseId) {
            return array();
        }

        return $task;
    }

    public function preCreateTaskCheck($task)
    {
        $this->getActivityService()->preCreateCheck($task['mediaType'], $task);
    }

    public function createTask($fields)
    {
        $fields = array_filter(
            $fields,
            function ($value) {
                if (is_array($value) || ctype_digit((string) $value)) {
                    return true;
                }

                return !empty($value);
            }
        );

        if ($this->invalidTask($fields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (!$this->getCourseService()->tryManageCourse($fields['fromCourseId'])) {
            $this->createNewException(TaskException::FORBIDDEN_CREATE_TASK());
        }

        $this->preCreateTaskCheck($fields);

        $this->beginTransaction();
        try {
            if (isset($fields['content'])) {
                $fields['content'] = $this->purifyHtml($fields['content'], true);
            }

            $fields = $this->createActivity($fields);
            $strategy = $this->createCourseStrategy($fields['courseId']);
            $task = $strategy->createTask($fields);
            $this->dispatchEvent('course.task.create', new Event($task));
            $this->commit();

            return $task;
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }
    }

    protected function createActivity($fields)
    {
        $activity = $this->getActivityService()->createActivity($fields);

        $fields['activityId'] = $activity['id'];
        $fields['createdUserId'] = $activity['fromUserId'];
        $fields['courseId'] = $activity['fromCourseId'];
        $fields['type'] = $fields['mediaType'];
        $fields['endTime'] = $activity['endTime'];

        if (in_array($activity['mediaType'], self::$mediaList)) {
            $media = json_decode($fields['media'], true);
            $fields['mediaSource'] = $media['source'];

            if ('video' === $activity['mediaType'] && 'self' == $fields['mediaSource']) {
                $this->getCourseService()->convertAudioByCourseIdAndMediaId($activity['fromCourseId'], $media['id']);
            }
        }

        return $fields;
    }

    protected function invalidTask($task)
    {
        if (!ArrayToolkit::requireds($task, array('title', 'fromCourseId'))) {
            return true;
        }

        return false;
    }

    public function preUpdateTaskCheck($taskId, $fields)
    {
        $task = $this->getTask($taskId);
        if (!$task) {
            $this->createNewException(TaskException::NOTFOUND_TASK());
        }

        $this->getActivityService()->preUpdateCheck($task['activityId'], $fields);
    }

    public function updateTask($id, $fields)
    {
        $oldTask = $task = $this->getTask($id);

        if (!$this->getCourseService()->tryManageCourse($task['courseId'])) {
            $this->createNewException(TaskException::FORBIDDEN_UPDATE_TASK());
        }

        $this->beginTransaction();
        try {
            $this->preUpdateTaskCheck($id, $fields);

            $activity = $this->getActivityService()->updateActivity($task['activityId'], $fields);

            if (in_array($activity['mediaType'], self::$mediaList)) {
                $media = json_decode($fields['media'], true);
                $fields['mediaSource'] = $media['source'];
            }

            $fields['endTime'] = $activity['endTime'];
            $strategy = $this->createCourseStrategy($task['courseId']);
            $task = $strategy->updateTask($id, $fields);
            $this->dispatchEvent('course.task.update', new Event($task, $oldTask));

            if ('download' == $task['type']) {
                $this->dispatchEvent('course.task.material.update', new Event($task, $oldTask));
            }

            $this->commit();

            return $task;
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }
    }

    public function publishTask($id)
    {
        $task = $this->getTask($id);

        if (!$this->getCourseService()->tryManageCourse($task['courseId'])) {
            $this->createNewException(TaskException::FORBIDDEN_PUBLISH_TASK());
        }

        if ('published' === $task['status']) {
            return;
        }

        if (!$this->canPublish($task['id'])) {
            return false;
        }

        $strategy = $this->createCourseStrategy($task['courseId']);

        $task = $strategy->publishTask($task);
        $this->dispatchEvent('course.task.publish', new Event($task));

        return $task;
    }

    public function publishTasksByCourseId($courseId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $tasks = $this->findTasksByCourseId($courseId);

        if (!empty($tasks)) {
            foreach ($tasks as $task) {
                if ('published' !== $task['status']) {
                    //mode存在且不等于lesson的任务会随着mode=lesson的任务发布，这里不应重复发布
                    if (!empty($task['mode']) && 'lesson' !== $task['mode']) {
                        continue;
                    }
                    if (!$this->canPublish($task['id'])) {
                        continue;
                    }
                    $this->publishTask($task['id']);
                }
            }
        }
    }

    protected function canPublish($taskId)
    {
        $jobName = 'course_task_create_sync_job_'.$taskId;

        $fireJobs = $this->getSchedulerService()->searchJobFires(
            array('job_name' => $jobName),
            array('id' => 'desc'),
            0,
            1
        );
        $syncCreateTaskFireJob = reset($fireJobs);

        if (!empty($syncCreateTaskFireJob) && in_array($syncCreateTaskFireJob['status'], array('executing', 'acquired'))) {
            return false;
        }

        return true;
    }

    public function unpublishTask($id)
    {
        $task = $this->getTask($id);

        if (!$this->getCourseService()->tryManageCourse($task['courseId'])) {
            $this->createNewException(TaskException::FORBIDDEN_UNPUBLISH_TASK());
        }

        if ('unpublished' === $task['status']) {
            $this->createNewException(TaskException::UNPUBLISHED_TASK());
        }

        $strategy = $this->createCourseStrategy($task['courseId']);
        $task = $strategy->unpublishTask($task);
        $this->dispatchEvent('course.task.unpublish', new Event($task));

        return $task;
    }

    public function updateSeq($id, $fields)
    {
        $fields = ArrayToolkit::parts(
            $fields,
            array(
                'seq',
                'categoryId',
                'number',
            )
        );
        $task = $this->getTaskDao()->update($id, $fields);
        $this->dispatchEvent('course.task.update', new Event($task));

        return $task;
    }

    public function updateTasks($ids, $fields)
    {
        $fields = ArrayToolkit::parts($fields, array('isFree'));

        foreach ($ids as $id) {
            $_task = $this->getTaskDao()->update($id, $fields);
            //xxx 这里可能影响执行效率：1. 批量处理，2. 仅仅是更新isFree，却会触发task的所有信息
            $this->dispatchEvent('course.task.update', new Event($_task));
        }

        return true;
    }

    public function deleteTask($id)
    {
        $task = $this->getTask($id);
        if (!$this->getCourseService()->tryManageCourse($task['courseId'])) {
            $this->createNewException(TaskException::FORBIDDEN_DELETE_TASK());
        }

        $this->dispatchEvent('course.task.delete.before', new Event($task));

        $this->beginTransaction();
        try {
            $result = $this->createCourseStrategy($task['courseId'])->deleteTask($task);
            $this->updateTaskName($task);

            $this->dispatchEvent('course.task.delete', new Event($task, array('user' => $this->getCurrentUser())));

            $this->commit();

            return $result;
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }
    }

    public function deleteTasksByCategoryId($courseId, $categoryId)
    {
        $lessonTasks = $this->getTaskDao()->findByCourseIdAndCategoryId($courseId, $categoryId);
        if (empty($lessonTasks)) {
            return;
        }
        foreach ($lessonTasks as $task) {
            $this->deleteTask($task['id']);
        }
    }

    public function findTasksByCourseId($courseId)
    {
        return $this->getTaskDao()->findByCourseId($courseId);
    }

    public function findTasksByCourseSetId($courseSetId)
    {
        return $this->getTaskDao()->findByCourseSetId($courseSetId);
    }

    public function findTasksByCourseIds($courseIds)
    {
        return $this->getTaskDao()->findByCourseIds($courseIds);
    }

    public function findTasksByActivityIds($activityIds)
    {
        $tasks = $this->getTaskDao()->findByActivityIds($activityIds);

        return ArrayToolkit::index($tasks, 'activityId');
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
        $tasks = $this->findTasksByCourseId($courseId);
        $activityIds = ArrayToolkit::column($tasks, 'activityId');
        $activities = $this->getActivityService()->findActivities($activityIds, true, 0);
        $activities = ArrayToolkit::index($activities, 'id');

        array_walk(
            $tasks,
            function (&$task) use ($activities) {
                $task['activity'] = $activities[$task['activityId']];
            }
        );

        return $tasks;
    }

    public function findTasksFetchActivityAndResultByCourseId($courseId)
    {
        $tasks = $this->findTasksFetchActivityByCourseId($courseId);
        if (empty($tasks)) {
            return array();
        }

        return $this->wrapTaskResultToTasks($courseId, $tasks);
    }

    public function wrapTaskResultToTasks($courseId, $tasks)
    {
        $taskIds = array_column($tasks, 'id');
        $taskResults = $this->getTaskResultService()->findUserTaskResultsByTaskIds($taskIds);
        $taskResults = ArrayToolkit::index($taskResults, 'courseTaskId');

        array_walk(
            $tasks,
            function (&$task) use ($taskResults) {
                $task['result'] = isset($taskResults[$task['id']]) ? $taskResults[$task['id']] : null;
            }
        );

        $user = $this->getCurrentUser();
        $teacher = $this->getMemberService()->isCourseTeacher($courseId, $user->getId());

        $course = $this->getCourseService()->getCourse($courseId);
        $isLock = false;
        $magicSetting = $this->getSettingService()->get('magic');
        foreach ($tasks as &$task) {
            if ('freeMode' == $course['learnMode']) {
                $task['lock'] = false;
            } else {
                $task = $this->setTaskLockStatus($tasks, $task, $teacher);
            }

            //设置第一个发布的任务为解锁的
            if (!$isLock && 'published' === $task['status']) {
                $task['lock'] = false;
                $isLock = true;
            }

            //计算剩余观看时长
            $shouldCalcWatchLimitRemaining = !empty($magicSetting['lesson_watch_limit']) && 'video' == $task['type'] && 'self' == $task['mediaSource'] && $course['watchLimit'];
            if ($shouldCalcWatchLimitRemaining) {
                if ($task['result']) {
                    $task['watchLimitRemaining'] = $course['watchLimit'] * $task['length'] - $task['result']['watchTime'];
                    $task['watchLimitRemaining'] = $task['watchLimitRemaining'] < 0 ? 0 : $task['watchLimitRemaining'];
                } else {
                    $task['watchLimitRemaining'] = $course['watchLimit'] * $task['length'];
                }
            }

            $isTryLookable = $course['tryLookable'] && 'video' == $task['type'] && !empty($task['ext']['file']) && 'cloud' === $task['ext']['file']['storage'];
            if ($isTryLookable) {
                $task['tryLookable'] = 1;
            } else {
                $task['tryLookable'] = 0;
            }
        }

        return $tasks;
    }

    protected function getPreTasks($tasks, $currentTask)
    {
        return array_filter(
            array_reverse($tasks),
            function ($task) use ($currentTask) {
                return $currentTask['seq'] > $task['seq'];
            }
        );
    }

    /**
     * 给定一个任务 ，判断前置解锁条件是完成.
     *
     * @param  $preTasks
     *
     * @return bool
     */
    public function isPreTasksIsFinished($preTasks)
    {
        $canLearnTask = true;

        foreach (array_values($preTasks) as $key => $preTask) {
            if ('published' !== $preTask['status']) {
                continue;
            }
            if ($preTask['isOptional']) {
                continue;
            }
            if ('live' === $preTask['type']) {
                if (time() > $preTask['endTime']) {
                    continue;
                }
            }
            if ('testpaper' === $preTask['type'] && $preTask['startTime']) {
                if (time() > $preTask['startTime'] + $preTask['activity']['ext']['limitedTime'] * 60) {
                    continue;
                }
            }

            $isTaskLearned = empty($preTask['result']) ? false : ('finish' === $preTask['result']['status']);
            if ($isTaskLearned) {
                continue;
            } else {
                $canLearnTask = false;
                break;
            }
        }

        return $canLearnTask;
    }

    public function findUserTeachCoursesTasksByCourseSetId($userId, $courseSetId)
    {
        $conditions = array(
            'userId' => $userId,
        );
        $myTeachCourses = $this->getCourseService()->findUserTeachCourses($conditions, 0, PHP_INT_MAX, true);

        $conditions = array(
            'courseIds' => ArrayToolkit::column($myTeachCourses, 'courseId'),
            'courseSetId' => $courseSetId,
        );
        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        return $this->findTasksByCourseIds(ArrayToolkit::column($courses, 'id'));
    }

    public function searchTasks($conditions, $orderBy, $start, $limit)
    {
        return $this->getTaskDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countTasks($conditions)
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
            'activityId' => $task['activityId'],
            'courseId' => $task['courseId'],
            'courseTaskId' => $task['id'],
            'userId' => $user['id'],
        );

        $taskResult = $this->getTaskResultService()->createTaskResult($taskResult);

        $this->dispatchEvent('course.task.start', new Event($taskResult));
    }

    public function doTask($taskId, $time = TaskService::LEARN_TIME_STEP)
    {
        $task = $this->tryTakeTask($taskId);

        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);

        if (empty($taskResult)) {
            $this->createNewException(TaskException::CAN_NOT_DO());
        }

        $this->getTaskResultService()->waveLearnTime($taskResult['id'], $time);
    }

    public function watchTask($taskId, $watchTime = TaskService::WATCH_TIME_STEP)
    {
        $task = $this->tryTakeTask($taskId);

        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);

        if (empty($taskResult)) {
            $this->createNewException(TaskException::CAN_NOT_DO());
        }

        $this->getTaskResultService()->waveWatchTime($taskResult['id'], $watchTime);
    }

    public function finishTask($taskId)
    {
        $this->tryTakeTask($taskId);

        if (!$this->isFinished($taskId)) {
            $this->createNewException(TaskException::CAN_NOT_FINISH());
        }

        return $this->finishTaskResult($taskId);
    }

    public function finishTaskResult($taskId)
    {
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);

        if (empty($taskResult)) {
            $task = $this->getTask($taskId);
            $activity = $this->getActivityService()->getActivity($task['activityId']);
            if ('live' === $activity['mediaType']) {
                $this->trigger($task['id'], 'start', array('task' => $task));
                $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);
            } else {
                $this->createNewException(TaskException::ACCESS_DENIED());
            }
        }

        if ('finish' === $taskResult['status']) {
            return $taskResult;
        }

        $update['updatedTime'] = time();
        $update['status'] = 'finish';
        $update['finishedTime'] = time();
        $taskResult = $this->getTaskResultService()->updateTaskResult($taskResult['id'], $update);
        $this->dispatchEvent('course.task.finish', new Event($taskResult, array('user' => $this->getCurrentUser())));

        return $taskResult;
    }

    public function findFreeTasksByCourseId($courseId)
    {
        $tasks = $this->getTaskDao()->findByCourseIdAndIsFree($courseId, $isFree = true);
        $tasks = ArrayToolkit::index($tasks, 'id');

        return $tasks;
    }

    /**
     * 设置当前任务最大可同时进行的人数  如直播任务等.
     *
     * @param  $taskId
     * @param  $maxNum
     *
     * @return mixed
     */
    public function setTaskMaxOnlineNum($taskId, $maxNum)
    {
        return $this->getTaskDao()->update($taskId, array('maxOnlineNum' => $maxNum));
    }

    /**
     * 统计当前时间以后每天的直播次数.
     *
     * @param  $limit
     *
     * @return array <string, int|string>
     */
    public function findFutureLiveDates($limit = 4)
    {
        return $this->getTaskDao()->findFutureLiveDates($limit);
    }

    public function findPublishedLivingTasksByCourseSetId($courseSetId)
    {
        $conditions = array(
            'fromCourseSetId' => $courseSetId,
            'type' => 'live',
            'status' => 'published',
            'startTime_LT' => time(),
            'endTime_GT' => time(),
        );

        return $this->searchTasks($conditions, array('startTime' => 'ASC'), 0, $this->countTasks($conditions));
    }

    public function findPublishedTasksByCourseSetId($courseSetId)
    {
        $conditions = array(
            'fromCourseSetId' => $courseSetId,
            'type' => 'live',
            'status' => 'published',
        );

        return $this->searchTasks($conditions, array('startTime' => 'ASC'), 0, $this->countTasks($conditions));
    }

    /**
     * 返回当前正在直播的直播任务
     *
     * @return array
     */
    public function findCurrentLiveTasks()
    {
        $condition = array(
            'startTime_LE' => time(),
            'endTime_GT' => time(),
            'type' => 'live',
            'status' => 'published',
        );

        return $this->searchTasks($condition, array('startTime' => 'ASC'), 0, $this->countTasks($condition));
    }

    /**
     * 返回当前将要直播的直播任务
     *
     * @return array
     */
    public function findFutureLiveTasks()
    {
        $condition = array(
            'startTime_GT' => time(),
            'endTime_LT' => strtotime(date('Y-m-d').' 23:59:59'),
            'type' => 'live',
            'status' => 'published',
        );

        return $this->searchTasks($condition, array('startTime' => 'ASC'), 0, $this->countTasks($condition));
    }

    /**
     * 返回过去直播过的教学计划ID.
     *
     * @return array
     */
    public function findPastLivedCourseSetIds()
    {
        $arrays = $this->getTaskDao()->findPastLivedCourseSetIds();

        return ArrayToolkit::column($arrays, 'fromCourseSetId');
    }

    public function isFinished($taskId)
    {
        $task = $this->getTask($taskId);

        return $this->getActivityService()->isFinished($task['activityId']);
    }

    public function tryTakeTask($taskId)
    {
        if (!$this->canLearnTask($taskId)) {
            $this->createNewException(TaskException::LOCKED_TASK());
        }
        $task = $this->getTask($taskId);

        if (empty($task)) {
            $this->createNewException(TaskException::NOTFOUND_TASK());
        }

        return $task;
    }

    public function getNextTask($taskId)
    {
        $task = $this->getTask($taskId);
        $course = $this->getCourseService()->getCourse($task['courseId']);

        $conditions = array(
            'courseId' => $task['courseId'],
            'status' => 'published',
        );
        if ('freeMode' === $course['learnMode']) {
            $taskResults = $this->getTaskResultService()->findUserFinishedTaskResultsByCourseId($course['id']);
            $finishTaskIds = ArrayToolkit::column($taskResults, 'courseTaskId');
            $electiveTaskIds = $this->getStartElectiveTaskIds($course['id']);

            $conditions['excludeIds'] = array_merge($finishTaskIds, $electiveTaskIds);
        } else {
            if ($task['isOptional']) {
                $taskResults = $this->getTaskResultService()->findUserFinishedTaskResultsByCourseId($course['id']);
                $finishTaskIds = ArrayToolkit::column($taskResults, 'courseTaskId');
                $conditions['excludeIds'] = $finishTaskIds;
            } else {
                $conditions['seq_GT'] = $task['seq'];
            }
        }

        //取得下一个发布的课时
        $nextTasks = $this->getTaskDao()->search($conditions, array('seq' => 'ASC'), 0, 1);

        if (empty($nextTasks)) {
            return array();
        }
        $nextTask = array_shift($nextTasks);

        //判断下一个课时是否课时学习
        if (!$this->canLearnTask($nextTask['id'])) {
            return array();
        }

        return $nextTask;
    }

    public function canLearnTask($taskId)
    {
        $task = $this->getTask($taskId);

        $this->getCourseService()->tryTakeCourse($task['courseId']);

        //check if has permission to course and task
        $isAllowed = false;
        if ($task['isFree']) {
            $isAllowed = true;
        } elseif ($this->getCourseService()->canTakeCourse($task['courseId'])) {
            $isAllowed = true;
        }
        if ($isAllowed) {
            return $this->createCourseStrategy($task['courseId'])->canLearnTask($task);
        }

        return false;
    }

    public function isTaskLearned($taskId)
    {
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);

        return empty($taskResult) ? false : ('finish' === $taskResult['status']);
    }

    public function getMaxSeqByCourseId($courseId)
    {
        return $this->getTaskDao()->getMaxSeqByCourseId($courseId);
    }

    public function getMaxNumberByCourseId($courseId)
    {
        return $this->getTaskDao()->getNumberSeqByCourseId($courseId);
    }

    public function getTaskByCourseIdAndActivityId($courseId, $activityId)
    {
        return $this->getTaskDao()->getTaskByCourseIdAndActivityId($courseId, $activityId);
    }

    public function countTasksByChpaterId($chapterId)
    {
        return $this->getTaskDao()->countByChpaterId($chapterId);
    }

    public function findTasksByChapterId($chapterId)
    {
        return $this->getTaskDao()->findByChapterId($chapterId);
    }

    public function findTasksFetchActivityByChapterId($chapterId)
    {
        $tasks = $this->findTasksByChapterId($chapterId);

        $activityIds = ArrayToolkit::column($tasks, 'activityId');
        $activities = $this->getActivityService()->findActivities($activityIds, true, 0);
        $activities = ArrayToolkit::index($activities, 'id');

        array_walk(
            $tasks,
            function (&$task) use ($activities) {
                $task['activity'] = $activities[$task['activityId']];
            }
        );

        return $tasks;
    }

    /**
     * @param  $courseId
     *
     * @return array tasks
     */
    public function findToLearnTasksByCourseId($courseId)
    {
        list($course) = $this->getCourseService()->tryTakeCourse($courseId);
        $toLearnTasks = $tasks = array();

        if (!in_array($course['learnMode'], array('freeMode', 'lockMode'))) {
            return $toLearnTasks;
        }

        if ('freeMode' === $course['learnMode']) {
            $toLearnTask = $this->getToLearnTaskWithFreeMode($courseId);
            if (!empty($toLearnTask)) {
                $toLearnTasks[] = $toLearnTask;
            }
        }
        if ('lockMode' === $course['learnMode']) {
            list($tasks, $toLearnTasks) = $this->getToLearnTasksWithLockMode($courseId);
            $toLearnTasks = $this->fillTaskResultAndLockStatus($toLearnTasks, $course, $tasks);
        }

        return $toLearnTasks;
    }

    /**
     * @param  $courseId
     *
     * @return array|mixed
     */
    public function findToLearnTasksByCourseIdForMission($courseId)
    {
        list($course) = $this->getCourseService()->tryTakeCourse($courseId);
        $toLearnTasks = $tasks = array();

        if (!in_array($course['learnMode'], array('freeMode', 'lockMode'))) {
            return $toLearnTasks;
        }
        list($tasks, $toLearnTasks) = $this->getToLearnTasksWithLockMode($courseId);

        $toLearnTasks = $this->fillTaskResultAndLockStatus($toLearnTasks, $course, $tasks);

        return $toLearnTasks;
    }

    public function getTimeSec($type)
    {
        $magicSetting = $this->getSettingService()->get('magic');
        $default = 'watch' == $type ? TaskService::WATCH_TIME_STEP : TaskService::LEARN_TIME_STEP;

        return empty($magicSetting[$type.'_time_sec']) ? $default : $magicSetting[$type.'_time_sec'];
    }

    protected function getToLearnTaskWithFreeMode($courseId)
    {
        $finishedTasks = $this->getTaskResultService()->findUserFinishedTaskResultsByCourseId($courseId);
        if (!empty($finishedTasks)) {
            $taskIds = ArrayToolkit::column($finishedTasks, 'courseTaskId');
            $electiveTaskIds = $this->getStartElectiveTaskIds($courseId);
            $taskIds = array_merge($taskIds, $electiveTaskIds);

            $conditions = array(
                'courseId' => $courseId,
                'status' => 'published',
                'excludeIds' => $taskIds,
            );

            $tasks = $this->searchTasks($conditions, array('seq' => 'ASC'), 0, 1);

            return empty($tasks) ? array() : array_shift($tasks);
        }

        $tasks = $this->findTasksByCourseId($courseId);

        return array_shift($tasks);
    }

    protected function getStartElectiveTaskIds($courseId)
    {
        $userTaskResults = $this->getTaskResultService()->findUserProgressingTaskResultByCourseId($courseId);
        $userTaskIds = ArrayToolkit::column($userTaskResults, 'courseTaskId');

        $conditions = array(
            'courseId' => $courseId,
            'status' => 'published',
            'isOptional' => 1,
        );

        $electiveTasks = $this->searchTasks($conditions, null, 0, PHP_INT_MAX);
        $electiveTaskIds = ArrayToolkit::column($electiveTasks, 'id');

        $electiveIds = array_intersect($userTaskIds, $electiveTaskIds);

        return empty($electiveIds) ? array() : $electiveIds;
    }

    protected function getToLearnTasksWithLockMode($courseId)
    {
        $toLearnTaskCount = 3;
        $taskResult = $this->getTaskResultService()->getUserLatestFinishedTaskResultByCourseId($courseId);
        $toLearnTasks = array();
        $course = $this->getCourseService()->getCourse($courseId);
        $taskConditions = array('courseId' => $courseId);
        if ($course['isHideUnpublish']) {
            $taskConditions['status'] = 'published';
        }

        //取出所有的任务
        $tasks = $this->getTaskDao()->search($taskConditions, array('seq' => 'ASC'), 0, PHP_INT_MAX);
        if (empty($taskResult)) {
            $toLearnTasks = $this->getTaskDao()->search(
                array('courseId' => $courseId, 'status' => 'published'),
                array('seq' => 'ASC'),
                0,
                $toLearnTaskCount
            );

            return array($tasks, $toLearnTasks);
        }

        if (count($tasks) <= $toLearnTaskCount) {
            $toLearnTasks = $tasks;

            return array($tasks, $toLearnTasks);
        }

        $previousTask = null;
        //向后取待学习的三个任务
        foreach ($tasks as $task) {
            if ($task['id'] == $taskResult['courseTaskId']) {
                $toLearnTasks[] = $task;
                $previousTask = $task;
            }
            if ($previousTask && $task['seq'] > $previousTask['seq'] && count($toLearnTasks) < $toLearnTaskCount) {
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
                if ($previousTask && $task['seq'] < $previousTask['seq'] && count($toLearnTasks) < $toLearnTaskCount) {
                    array_unshift($toLearnTasks, $task);
                    $previousTask = $task;
                }
            }
        }

        return array($tasks, $toLearnTasks);
    }

    public function trigger($id, $eventName, $data = array())
    {
        $task = $this->getTask($id);
        $data['task'] = $task;
        $this->getActivityService()->trigger($task['activityId'], $eventName, $data);

        return $this->getTaskResultService()->getUserTaskResultByTaskId($id);
    }

    public function sumCourseSetLearnedTimeByCourseSetId($courseSetId)
    {
        return $this->getTaskDao()->sumCourseSetLearnedTimeByCourseSetId($courseSetId);
    }

    public function analysisTaskDataByTime($startTime, $endTime)
    {
        return $this->getTaskDao()->analysisTaskDataByTime($startTime, $endTime);
    }

    /**
     * 获取用户最近进行的一个任务
     *
     * @param int $userId
     *
     * @return array
     */
    public function getUserRecentlyStartTask($userId)
    {
        $results = $this->getTaskResultService()->searchTaskResults(
            array(
                'userId' => $userId,
            ),
            array(
                'createdTime' => 'DESC',
            ),
            0,
            1
        );
        $result = array_shift($results);
        if (empty($result)) {
            return array();
        }

        return $this->getTask($result['courseTaskId']);
    }

    public function batchCreateTasks($tasks)
    {
        if (empty($tasks)) {
            return array();
        }

        return $this->getTaskDao()->batchCreate($tasks);
    }

    public function getTodayLiveCourseNumber()
    {
        $user = $this->getCurrentUser();
        $liveCourseNumber = 0;
        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        $tasks = $this->searchTasks(
            array('type' => 'live', 'startTime_GE' => $beginToday, 'endTime_LT' => $endToday, 'status' => 'published'),
            array(),
            0,
            PHP_INT_MAX
        );
        foreach ($tasks as $task) {
            $members = $this->getMemberService()->searchMembers(
                array('courseId' => $task['courseId'], 'role' => 'teacher'),
                array(),
                0,
                PHP_INT_MAX
            );
            $userIds = ArrayToolkit::column($members, 'userId');
            if (empty($userIds) || !in_array($user['id'], $userIds)) {
                continue;
            }
            $course = $this->getCourseService()->getCourse($task['courseId']);
            if (!empty($course) && 'published' == $course['status']) {
                $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
                if (!empty($courseSet) && 'published' == $courseSet['status']) {
                    $liveCourseNumber = $liveCourseNumber + 1;
                }
            }
        }

        return $liveCourseNumber;
    }

    public function updateTasksOptionalByLessonId($lessonId, $isOptional = 0)
    {
        $lesson = $this->getCourseLessonService()->getLesson($lessonId);

        if (empty($lesson) || 'lesson' != $lesson['type']) {
            $this->createNewException(TaskException::LESSONID_INVALID());
        }

        $this->getCourseService()->tryManageCourse($lesson['courseId']);

        $tasks = $this->findTasksByChapterId($lessonId);

        foreach ($tasks as $task) {
            $newTask = $this->getTaskDao()->update($task['id'], array('isOptional' => $isOptional));

            $action = 1 == $isOptional ? 'task_set_optional' : 'task_unset_optional';

            $infoData = array(
                'courseId' => $task['courseId'],
                'title' => $task['title'],
            );

            $this->getLogService()->info('course', $action, "更新任务《{$task['title']}》的选修状态", $infoData);
            $this->dispatchEvent('course.task.updateOptional', new Event($newTask, $task));
        }
    }

    public function countLessonsWithMultipleTasks($courseId)
    {
        return $this->getTaskDao()->countLessonsWithMultipleTasks($courseId);
    }

    /**
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }

    /**
     * @param  $courseId
     *
     * @throws CourseException
     * @throws \Exception
     *
     * @return CourseStrategy
     */
    protected function createCourseStrategy($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        return $this->biz['course.strategy_context']->createStrategy($course['courseType']);
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

    /**
     * @param  $tasks
     * @param  $task
     * @param  $teacher
     *
     * @return mixed
     */
    protected function setTaskLockStatus($tasks, $task, $teacher)
    {
        //不是课程教师，无权限管理
        if ($teacher) {
            $task['lock'] = false;

            return $task;
        }

        $preTasks = $this->getPreTasks($tasks, $task);
        if (empty($preTasks)) {
            $task['lock'] = false;
        }

        $finish = $this->isPreTasksIsFinished($preTasks);
        //当前任务未完成且前一个问题未完成则锁定
        $task['lock'] = !$finish;

        //选修任务不需要判断解锁条件
        if ($task['isOptional']) {
            $task['lock'] = false;
        }

        if ('live' === $task['type']) {
            $task['lock'] = false;
        }

        if ('testpaper' === $task['type'] && $task['startTime']) {
            $task['lock'] = false;
        }

        //如果该任务已经完成则忽略其他的条件
        if (isset($task['result']['status']) && ('finish' === $task['result']['status'])) {
            $task['lock'] = false;
        }

        return $task;
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @param  $toLearnTasks
     * @param  $course
     * @param  $tasks
     *
     * @return mixed
     */
    protected function fillTaskResultAndLockStatus($toLearnTasks, $course, $tasks)
    {
        $activityIds = ArrayToolkit::column($tasks, 'activityId');
        $activities = $this->getActivityService()->findActivities($activityIds, true, 0);
        $activities = ArrayToolkit::index($activities, 'id');

        $taskIds = ArrayToolkit::column($tasks, 'id');
        $taskResults = $this->getTaskResultService()->findUserTaskResultsByTaskIds($taskIds);
        $taskResults = ArrayToolkit::index($taskResults, 'courseTaskId');

        array_walk(
            $tasks,
            function (&$task) use ($taskResults, $activities) {
                $task['result'] = isset($taskResults[$task['id']]) ? $taskResults[$task['id']] : null;
                $task['activity'] = $activities[$task['activityId']];
            }
        );

        $user = $this->getCurrentUser();
        $teacher = $this->getMemberService()->isCourseTeacher($course['id'], $user->getId());

        //设置任务是否解锁
        foreach ($toLearnTasks as &$toLearnTask) {
            $toLearnTask['activity'] = $activities[$toLearnTask['activityId']];
            $toLearnTask['result'] = isset($taskResults[$toLearnTask['id']]) ? $taskResults[$toLearnTask['id']] : null;
            if ('lockMode' === $course['learnMode']) {
                $toLearnTask = $this->setTaskLockStatus($tasks, $toLearnTask, $teacher);
            }
        }

        return $toLearnTasks;
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getCourseLessonService()
    {
        return $this->createService('Course:LessonService');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    /*
     * 所属课时只有一个任务时，修改任务名称，改为课时名称
     */
    private function updateTaskName($task)
    {
        $leftTaskCount = $this->countTasks(array('categoryId' => $task['categoryId']));
        if (1 == $leftTaskCount) {
            $leftTasks = $this->searchTasks(array('categoryId' => $task['categoryId']), array('id' => 'asc'), 0, 1);
            $actualTask = $leftTasks[0];
            $chapter = $this->getCourseService()->getChapter($task['courseId'], $task['categoryId']);
            $this->getTaskDao()->update($actualTask['id'], array('title' => $chapter['title']));
        }
    }
}
