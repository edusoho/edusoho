<?php

namespace Biz\Task\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Service\ActivityService;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\CourseStrategy;
use Biz\Task\TaskException;
use Biz\Visualization\Service\ActivityLearnDataService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;

class TaskServiceImpl extends BaseService implements TaskService
{
    /**
     * @var array
     *            包含序列化字段的学习类型，mediaType
     */
    private static $mediaList = ['video', 'audio', 'doc', 'ppt', 'flash'];

    public function getTask($id)
    {
        return $this->getTaskDao()->get($id);
    }

    public function getCourseTask($courseId, $id)
    {
        $task = $this->getTaskDao()->get($id);
        if (empty($task) || $task['courseId'] != $courseId) {
            return [];
        }

        return $task;
    }

    public function getCourseTaskByCourseIdAndCopyId($courseId, $copyId)
    {
        $task = $this->getTaskDao()->getByCourseIdAndCopyId($courseId, $copyId);
        if (empty($task) || $task['courseId'] != $courseId) {
            return [];
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
            $task = array_merge($fields, $task);
            $this->dispatchEvent('course.task.create', new Event($task));
            $this->commit();

            return $task;
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }
    }

    public function getRecentLiveTaskStatus($courseId)
    {
        $tasks = $this->searchTasks(['status' => 'published', 'type' => 'live', 'courseId' => $courseId], ['startTime' => 'ASC'], 0, PHP_INT_MAX);
        if (0 == count($tasks)) {
            return 'null';
        }

        if (1 == count($tasks)) {
            $task = array_shift($tasks);
            $status = $this->filterLiveTaskStatus($task['startTime'], $task['endTime']);

            return (!$this->hasReplay($task['activityId'])) ? $status : 'hasReplay';
        }

        foreach ($tasks as $task) {
            $status = $this->filterLiveTaskStatus($task['startTime'], $task['endTime']);
            if ('living' == $status) {
                $hasLivingTask = true;

                return $status;
            }

            if ('ahead' == $status && !$hasLivingTask) {
                $hasAheadTask = true;

                $status = 'ahead';
            }

            $hasReplay = $this->hasReplay($task['activityId']);

            if ('end' == $status && !$hasAheadTask && $hasReplay) {
                $hasEndTaskAndHasReplay = true;

                $status = 'hasReplay';
            }

            if ('end' == $status && !$hasEndTaskAndHasReplay && !$hasReplay) {
                $status = 'end';
            }
        }

        return $status;
    }

    protected function hasReplay($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId, true);

        return (isset($activity['ext']['replayStatus']) && ('generated' == $activity['ext']['replayStatus'] || 'videoGenerated' == $activity['ext']['replayStatus'])) ? true : false;
    }

    protected function filterLiveTaskStatus($startTime, $endTime)
    {
        if ($startTime <= time() && time() <= $endTime) {
            return 'living';
        }

        if (time() > $endTime) {
            return 'end';
        }

        if ($startTime > time()) {
            return 'ahead';
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
        if (!ArrayToolkit::requireds($task, ['title', 'fromCourseId'])) {
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

            if (isset($fields['content'])) {
                $fields['content'] = $this->purifyHtml($fields['content'], true);
            }

            $activity = $this->getActivityService()->updateActivity($task['activityId'], $fields);

            if (in_array($activity['mediaType'], self::$mediaList)) {
                $media = json_decode($fields['media'], true);
                $fields['mediaSource'] = $media['source'];
            }

            $fields['endTime'] = $activity['endTime'];
            $strategy = $this->createCourseStrategy($task['courseId']);
            $task = $strategy->updateTask($id, $fields);
            $task = array_merge($fields, $task);
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
            $this->createNewException(TaskException::FORBIDDEN_PUBLISH_SYNC_TASK());
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
            ['job_name' => $jobName],
            ['id' => 'desc'],
            0,
            1
        );
        $syncCreateTaskFireJob = reset($fireJobs);

        if (!empty($syncCreateTaskFireJob) && in_array($syncCreateTaskFireJob['status'], ['executing', 'acquired'])) {
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
            [
                'seq',
                'categoryId',
                'number',
            ]
        );
        $task = $this->getTaskDao()->update($id, $fields);
        $this->dispatchEvent('course.task.update', new Event($task));

        return $task;
    }

    public function updateTasks($ids, $fields)
    {
        $fields = ArrayToolkit::parts($fields, ['isFree']);

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
            $this->dispatchEvent('course.task.delete', new Event($task, ['user' => $this->getCurrentUser()]));
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

        try {
            $this->biz['db']->beginTransaction();

            foreach ($lessonTasks as $task) {
                $this->deleteTask($task['id']);
            }

            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
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

    public function findTasksByCourseSetIds($courseSetIds)
    {
        return $this->getTaskDao()->findByCourseSetIds($courseSetIds);
    }

    public function findTasksByCategoryIds($categoryIds)
    {
        if (empty($categoryIds)) {
            return [];
        }

        return $this->getTaskDao()->findByCategoryIds($categoryIds);
    }

    public function findTasksByCourseIds($courseIds)
    {
        return $this->getTaskDao()->findByCourseIds($courseIds);
    }

    public function findTasksByCourseIdAndType($courseId, $type)
    {
        return $this->getTaskDao()->findByCourseIdAndType($courseId, $type);
    }

    public function findTasksByActivityIds($activityIds)
    {
        $tasks = $this->getTaskDao()->findByActivityIds($activityIds);

        return ArrayToolkit::index($tasks, 'activityId');
    }

    public function countTasksByCourseId($courseId)
    {
        return $this->getTaskDao()->count(['courseId' => $courseId]);
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
            return [];
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
            $isTryLookable = $course['tryLookable'] && 'video' == $task['type'] && !empty($task['activity']['ext']['file']) && 'cloud' === $task['activity']['ext']['file']['storage'];
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
        $conditions = [
            'userId' => $userId,
        ];
        $myTeachCourses = $this->getCourseService()->findUserTeachCourses($conditions, 0, PHP_INT_MAX, true);

        $conditions = [
            'courseIds' => ArrayToolkit::column($myTeachCourses, 'courseId'),
            'courseSetId' => $courseSetId,
        ];
        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            ['createdTime' => 'DESC'],
            0,
            PHP_INT_MAX
        );

        return $this->findTasksByCourseIds(ArrayToolkit::column($courses, 'id'));
    }

    public function searchTasks($conditions, $orderBy, $start, $limit, $columns = [])
    {
        return $this->getTaskDao()->search($conditions, $orderBy, $start, $limit, $columns);
    }

    public function findTestpapers($tasks, $type)
    {
        if (empty($tasks)) {
            return [$tasks, []];
        }

        $activityIds = ArrayToolkit::column($tasks, 'activityId');
        $activities = $this->getActivityService()->findActivities($activityIds);
        $activities = ArrayToolkit::index($activities, 'id');

        if ('testpaper' == $type) {
            $testpaperActivityIds = ArrayToolkit::column($activities, 'mediaId');
            $testpaperActivities = $this->getTestpaperActivityService()->findActivitiesByIds($testpaperActivityIds);
            $testpaperActivities = ArrayToolkit::index($testpaperActivities, 'id');
            $ids = ArrayToolkit::column($testpaperActivities, 'mediaId');

            array_walk($tasks, function (&$task, $key) use ($activities, $testpaperActivities) {
                $activity = $activities[$task['activityId']];
                $task['testId'] = $testpaperActivities[$activity['mediaId']]['mediaId'];
                $task['answerSceneId'] = $testpaperActivities[$activity['mediaId']]['answerSceneId'];
            });
        } else {
            $homeworkActivityIds = ArrayToolkit::column($activities, 'mediaId');
            $homeworkActivities = $this->getHomeworkActivityService()->findByIds($homeworkActivityIds);
            $homeworkActivities = ArrayToolkit::index($homeworkActivities, 'id');
            $ids = ArrayToolkit::column($homeworkActivities, 'assessmentId');

            array_walk($tasks, function (&$task, $key) use ($activities, $homeworkActivities) {
                $activity = $activities[$task['activityId']];
                $task['testId'] = $homeworkActivities[$activity['mediaId']]['assessmentId'];
                $task['answerSceneId'] = $homeworkActivities[$activity['mediaId']]['answerSceneId'];
            });
        }

        $testpapers = $this->getAssessmentService()->findAssessmentsByIds($ids);

        if (empty($testpapers)) {
            return [$activities, []];
        }

        return [$tasks, $testpapers];
    }

    // 搜索课时任务及相关数据统计
    public function searchTasksWithStatistics(array $conditions, $orderBy, $start, $limit)
    {
        $tasks = $this->getTaskDao()->search($conditions, $orderBy, $start, $limit);
        if (empty($tasks)) {
            return $tasks;
        }
        $sumlearnedTimeGroupByTaskIds = $this->getActivityLearnDataService()->sumLearnedTimeGroupByTaskIds(ArrayToolkit::column($tasks, 'id'));
        $activities = $this->getActivityService()->findActivities(array_column($tasks, 'activityId'), true, 0);
        $activities = array_column($activities, null, 'id');
        foreach ($tasks as &$task) {
            $task['finishedNum'] = $this->getTaskResultService()->countTaskResults(
                ['status' => 'finish', 'courseTaskId' => $task['id']]
            );
            $task['studentNum'] = $this->getTaskResultService()->countTaskResults(['courseTaskId' => $task['id']]);
            $sumLearnedTime = empty($sumlearnedTimeGroupByTaskIds[$task['id']]) ? 0 : $sumlearnedTimeGroupByTaskIds[$task['id']]['learnedTime'];
            $task['sumLearnedTime'] = round($sumLearnedTime / 60, 1);
            $task['avgLearnedTime'] = 0 == $task['studentNum'] ? 0 : round($sumLearnedTime / $task['studentNum'] / 60, 1);
            $task['activity'] = empty($activities[$task['activityId']]) ? [] : $activities[$task['activityId']];
            if ('testpaper' === $task['type'] && !empty($task['activity'])) {
                $activity = $task['activity'];
                $score = $this->getAnswerSceneService()->getAnswerSceneReport($activity['ext']['answerSceneId']);
                $task['score'] = $score['avg_score'];
            }
        }

        return $tasks;
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

        $taskResult = [
            'activityId' => $task['activityId'],
            'courseId' => $task['courseId'],
            'courseTaskId' => $task['id'],
            'userId' => $user['id'],
        ];

        $taskResult = $this->getTaskResultService()->createTaskResult($taskResult);

        $this->dispatchEvent('course.task.start', new Event($taskResult));

        return $taskResult;
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

        if (empty($watchTime)) {
            return;
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
            if (in_array($activity['mediaType'], ['live', 'replay'])) {
                $this->trigger($task['id'], 'start', ['task' => $task]);
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
        $this->dispatchEvent('course.task.finish', new Event($taskResult, ['user' => $this->getCurrentUser()]));

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
        return $this->getTaskDao()->update($taskId, ['maxOnlineNum' => $maxNum]);
    }

    public function findPublishedLivingTasksByCourseSetId($courseSetId)
    {
        $conditions = [
            'fromCourseSetId' => $courseSetId,
            'type' => 'live',
            'status' => 'published',
            'startTime_LT' => time(),
            'endTime_GT' => time(),
        ];

        return $this->searchTasks($conditions, ['startTime' => 'ASC'], 0, $this->countTasks($conditions));
    }

    public function findPublishedTasksByCourseSetId($courseSetId)
    {
        $conditions = [
            'fromCourseSetId' => $courseSetId,
            'type' => 'live',
            'status' => 'published',
        ];

        return $this->searchTasks($conditions, ['startTime' => 'ASC'], 0, $this->countTasks($conditions));
    }

    public function getUserCurrentPublishedLiveTask($userId, $startTime, $endBeforeRange)
    {
        return $this->getTaskDao()->getUserCurrentPublishedLiveTaskByTimeRange($userId, $startTime, $endBeforeRange);
    }

    /**
     * 返回当前正在直播的直播任务
     *
     * @return array
     */
    public function findCurrentLiveTasks()
    {
        $condition = [
            'startTime_LE' => time(),
            'endTime_GT' => time(),
            'type' => 'live',
            'status' => 'published',
        ];

        return $this->searchTasks($condition, ['startTime' => 'ASC'], 0, $this->countTasks($condition));
    }

    /**
     * 返回当前将要直播的直播任务
     *
     * @return array
     */
    public function findFutureLiveTasks()
    {
        $condition = [
            'startTime_GT' => time(),
            'endTime_LT' => strtotime(date('Y-m-d').' 23:59:59'),
            'type' => 'live',
            'status' => 'published',
        ];

        return $this->searchTasks($condition, ['startTime' => 'ASC'], 0, $this->countTasks($condition));
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

        $conditions = [
            'courseId' => $task['courseId'],
            'status' => 'published',
        ];
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
        $nextTasks = $this->getTaskDao()->search($conditions, ['seq' => 'ASC'], 0, 1);

        if (empty($nextTasks)) {
            return [];
        }
        $nextTask = array_shift($nextTasks);

        //判断下一个课时是否课时学习
        if (!$this->canLearnTask($nextTask['id'])) {
            return [];
        }

        return $nextTask;
    }

    public function canLearnTask($taskId)
    {
        $task = $this->getTask($taskId);

        $this->getCourseService()->tryTakeCourse($task['courseId']);

        //check if has permission to course and task
        $isAllowed = false;
        if ($task['isFree'] || $this->getCourseService()->canTakeCourse($task['courseId'])) {
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
        $toLearnTasks = $tasks = [];

        if (!in_array($course['learnMode'], ['freeMode', 'lockMode'])) {
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
        $toLearnTasks = $tasks = [];

        if (!in_array($course['learnMode'], ['freeMode', 'lockMode'])) {
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

            $conditions = [
                'courseId' => $courseId,
                'status' => 'published',
                'excludeIds' => $taskIds,
            ];

            $tasks = $this->searchTasks($conditions, ['seq' => 'ASC'], 0, 1);

            return empty($tasks) ? [] : array_shift($tasks);
        }

        $tasks = $this->findTasksByCourseId($courseId);

        return array_shift($tasks);
    }

    protected function getStartElectiveTaskIds($courseId)
    {
        $userTaskResults = $this->getTaskResultService()->findUserProgressingTaskResultByCourseId($courseId);
        $userTaskIds = ArrayToolkit::column($userTaskResults, 'courseTaskId');

        $conditions = [
            'courseId' => $courseId,
            'status' => 'published',
            'isOptional' => 1,
        ];

        $electiveTasks = $this->searchTasks($conditions, null, 0, PHP_INT_MAX);
        $electiveTaskIds = ArrayToolkit::column($electiveTasks, 'id');

        $electiveIds = array_intersect($userTaskIds, $electiveTaskIds);

        return empty($electiveIds) ? [] : $electiveIds;
    }

    protected function getToLearnTasksWithLockMode($courseId)
    {
        $toLearnTaskCount = 3;
        $taskResult = $this->getTaskResultService()->getUserLatestFinishedTaskResultByCourseId($courseId);
        $toLearnTasks = [];
        $course = $this->getCourseService()->getCourse($courseId);
        $taskConditions = ['courseId' => $courseId];
        if ($course['isHideUnpublish']) {
            $taskConditions['status'] = 'published';
        }

        //取出所有的任务
        $tasks = $this->getTaskDao()->search($taskConditions, ['seq' => 'ASC'], 0, PHP_INT_MAX);
        if (empty($taskResult)) {
            $toLearnTasks = $this->getTaskDao()->search(
                ['courseId' => $courseId, 'status' => 'published', 'isOptional' => 0],
                ['seq' => 'ASC'],
                0,
                $toLearnTaskCount
            );

            return [$tasks, $toLearnTasks];
        }

        if (count($tasks) <= $toLearnTaskCount) {
            $toLearnTasks = $tasks;

            return [$tasks, $toLearnTasks];
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

        return [$tasks, $toLearnTasks];
    }

    public function trigger($id, $eventName, $data = [])
    {
        $task = $this->getTask($id);
        $data['task'] = $task;
        $data['taskId'] = $task['id'];
        $this->getActivityService()->trigger($task['activityId'], $eventName, $data);

        return $this->getTaskResultService()->getUserTaskResultByTaskId($id);
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
            [
                'userId' => $userId,
            ],
            [
                'createdTime' => 'DESC',
            ],
            0,
            1
        );
        $result = array_shift($results);
        if (empty($result)) {
            return [];
        }

        return $this->getTask($result['courseTaskId']);
    }

    public function batchCreateTasks($tasks)
    {
        if (empty($tasks)) {
            return [];
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
            ['type' => 'live', 'startTime_GE' => $beginToday, 'endTime_LT' => $endToday, 'status' => 'published'],
            [],
            0,
            PHP_INT_MAX
        );
        foreach ($tasks as $task) {
            $members = $this->getMemberService()->searchMembers(
                ['courseId' => $task['courseId'], 'role' => 'teacher'],
                [],
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
            $newTask = $this->getTaskDao()->update($task['id'], ['isOptional' => $isOptional]);

            $action = 1 == $isOptional ? 'task_set_optional' : 'task_unset_optional';

            $infoData = [
                'courseId' => $task['courseId'],
                'title' => $task['title'],
            ];

            $this->getLogService()->info('course', $action, "更新任务《{$task['title']}》的选修状态", $infoData);
            $this->dispatchEvent('course.task.updateOptional', new Event($newTask, $task));
        }
    }

    public function countLessonsWithMultipleTasks($courseId)
    {
        return $this->getTaskDao()->countLessonsWithMultipleTasks($courseId);
    }

    public function canStartTask($taskId)
    {
        $task = $this->getTask($taskId);
        if (empty($task)) {
            return false;
        }

        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);
        if ($taskResult) {
            return false;
        }

        $course = $this->getCourseService()->getCourse($task['courseId']);
        if ('0' === $course['enableFinish']) {
            $wrappedTasks = ArrayToolkit::index(
                $this->wrapTaskResultToTasks(
                    $course['id'],
                    $this->findTasksByCourseId($course['id'])
                ),
                'id'
            );

            if ($wrappedTasks[$taskId]['lock']) {
                return false;
            }
        }

        return true;
    }

    public function findTasksByCopyIdAndLockedCourseIds($copyId, $courseIds)
    {
        return $this->getTaskDao()->findByCopyIdAndLockedCourseIds($copyId, $courseIds);
    }

    public function syncClassroomCourseTasks($courseId, $real)
    {
        $output = [];
        $course = $this->getCourseService()->getCourse($courseId);
        if (1 != $course['locked']) {
            $output[] = '<info>输入的课程不是班级课程，执行结束</info>';

            return $output;
        }
        $copiedTaskIds = array_column($this->searchTasks(['courseId' => $courseId], [], 0, PHP_INT_MAX, ['copyId']), 'copyId');
        $originTaskIds = array_column($this->searchTasks(['courseId' => $course['parentId']], [], 0, PHP_INT_MAX, ['id']), 'id');
        $toCopyTaskIds = array_diff($originTaskIds, $copiedTaskIds);
        if (empty($toCopyTaskIds)) {
            $output[] = '<info>不存在问题数据,无需处理</info>';
            $output[] = '<info>结束</info>';

            return $output;
        }
        foreach ($toCopyTaskIds as $toCopyTaskId) {
            $originTask = $this->getTask($toCopyTaskId);
            $output[] = "<info>未同步创建的任务:{$originTask['id']},《{$originTask['title']}》</info>";
            if (!$real) {
                continue;
            }
            $copiedChapter = $this->getCourseChapterDao()->getByCopyIdAndLockedCourseId($originTask['categoryId'], $courseId);
            if (empty($copiedChapter)) {
                $originChapter = $this->getCourseChapterDao()->get($originTask['categoryId']);
                $originChapter['copyId'] = $originChapter['id'];
                unset($originChapter['id']);
                $originChapter['courseId'] = $courseId;
                $copiedChapter = $this->getCourseChapterDao()->create($originChapter);
            }
            $copiedActivity = $this->getActivityDao()->getByCopyIdAndCourseSetId($originTask['activityId'], $course['courseSetId']);
            if (empty($copiedActivity)) {
                $originActivity = $this->getActivityDao()->get($originTask['activityId']);
                $originActivity['copyId'] = $originActivity['id'];
                unset($originActivity['id']);
                $originActivity['fromCourseId'] = $courseId;
                $originActivity['fromCourseSetId'] = $course['courseSetId'];
                $originActivity['createdTime'] = $copiedChapter['createdTime'];
                $originActivity['updatedTime'] = $copiedChapter['updatedTime'];
                $copiedActivity = $this->getActivityDao()->create($originActivity);
            }
            $task = ArrayToolkit::parts($originTask, [
                'seq',
                'title',
                'isFree',
                'isOptional',
                'startTime',
                'endTime',
                'mode',
                'isLesson',
                'status',
                'number',
                'type',
                'mediaSource',
                'maxOnlineNum',
                'length',
                'syncId',
            ]);
            $task['courseId'] = $courseId;
            $task['categoryId'] = $copiedChapter['id'];
            $task['activityId'] = $copiedActivity['id'];
            $task['copyId'] = $toCopyTaskId;
            $task['fromCourseSetId'] = $course['courseSetId'];
            $task['createdUserId'] = $copiedActivity['fromUserId'];
            $task['createdTime'] = $copiedChapter['createdTime'];
            $task['updatedTime'] = $copiedChapter['updatedTime'];
            $task = $this->getTaskDao()->create($task);
            $output[] = "<info>创建未同步的任务:{$task['id']}成功</info>";
        }

        $output[] = '<info>结束</info>';
        return $output;
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

    /**
     * @return MemberService
     */
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
        $leftTaskCount = $this->countTasks(['categoryId' => $task['categoryId']]);
        if (1 == $leftTaskCount) {
            $leftTasks = $this->searchTasks(['categoryId' => $task['categoryId']], ['id' => 'asc'], 0, 1);
            $actualTask = $leftTasks[0];
            $chapter = $this->getCourseService()->getChapter($task['courseId'], $task['categoryId']);
            $this->getTaskDao()->update($actualTask['id'], ['title' => $chapter['title']]);
        }
    }

    /**
     * @return ActivityLearnDataService
     */
    protected function getActivityLearnDataService()
    {
        return $this->createService('Visualization:ActivityLearnDataService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->createService('ItemBank:Answer:AnswerSceneService');
    }

    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }

    protected function getHomeworkActivityService()
    {
        return $this->createService('Activity:HomeworkActivityService');
    }

    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return CourseChapterDao
     */
    protected function getCourseChapterDao()
    {
        return $this->biz->dao('Course:CourseChapterDao');
    }

    /**
     * @return ActivityDao
     */
    protected function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }
}
