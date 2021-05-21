<?php

namespace Biz\Task\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Course\Service\CourseService;
use Biz\System\Service\LogService;
use Biz\Task\Dao\TaskResultDao;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\User\UserException;

class TaskResultServiceImpl extends BaseService implements TaskResultService
{
    public function countTaskNumGroupByUserId($conditions)
    {
        $result = $this->getTaskResultDao()->countTaskNumGroupByUserId($conditions);

        return ArrayToolkit::index($result, 'userId');
    }

    public function analysisCompletedTaskDataByTime($startTime, $endTime)
    {
        return $this->getTaskResultDao()->analysisCompletedTaskDataByTime($startTime, $endTime);
    }

    public function findUserTaskResultsByCourseId($courseId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        return $this->getTaskResultDao()->findByCourseIdAndUserId($courseId, $user['id']);
    }

    public function getUserTaskResultByTaskId($taskId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        return $this->getTaskResultDao()->getByTaskIdAndUserId($taskId, $user['id']);
    }

    public function deleteUserTaskResultByTaskId($taskId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        return $this->getTaskResultDao()->deleteByTaskIdAndUserId($taskId, $user['id']);
    }

    public function deleteTaskResultsByTaskId($taskId)
    {
        return $this->getTaskResultDao()->deleteByTaskId($taskId);
    }

    public function getTaskResult($resultId)
    {
        return $this->getTaskResultDao()->get($resultId);
    }

    public function createTaskResult($taskResult)
    {
        ArrayToolkit::requireds($taskResult, [
            'activityId',
            'courseId',
            'courseTaskId',
            'userId',
        ]);

        $user = $this->biz['user'];

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $taskResult['status'] = 'start';

        return $this->getTaskResultDao()->create($taskResult);
    }

    public function updateTaskResult($id, $taskResult)
    {
        return $this->getTaskResultDao()->update($id, $taskResult);
    }

    public function waveLearnTime($id, $time)
    {
        return $this->getTaskResultDao()->wave([$id], [
            'time' => $time,
        ]);
    }

    public function waveWatchTime($id, $watchTime)
    {
        $maxAllowWatchTime = 200 * 60;
        if ($watchTime <= $maxAllowWatchTime) {
            $this->getTaskResultDao()->wave([$id], [
                'watchTime' => $watchTime,
            ]);

            return $this->getTaskResultDao()->get($id);
        }
    }

    public function checkUserWatchNum($taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);
        $course = $this->getCourseService()->getCourse($task['courseId']);

        //只有视频课程才限制观看时长
        if (empty($course['watchLimit']) || 'video' !== $task['type']) {
            return ['status' => 'ignore'];
        }

        $taskResult = $this->getUserTaskResultByTaskId($taskId);
        $watchLimitTime = $task['length'] * $course['watchLimit'];

        if (empty($taskResult)) {
            return ['status' => 'ok', 'watchedTime' => 0, 'watchLimitTime' => $watchLimitTime];
        }

        if ($taskResult['watchTime'] < $watchLimitTime) {
            return ['status' => 'ok', 'watchedTime' => $taskResult['watchTime'], 'watchLimitTime' => $watchLimitTime];
        }

        return ['status' => 'error', 'watchedTime' => $taskResult['watchTime'], 'watchLimitTime' => $watchLimitTime];
    }

    public function findUserProgressingTaskResultByActivityId($activityId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $conditions = [
            'activityId' => $activityId,
            'userId' => $user['id'],
            'status' => 'start',
        ];

        $count = $this->getTaskResultDao()->count($conditions);

        return $this->getTaskResultDao()->search($conditions, ['createdTime' => 'DESC'], 0, $count);
    }

    public function findUserProgressingTaskResultByCourseId($courseId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $conditions = [
            'courseId' => $courseId,
            'userId' => $user['id'],
            'status' => 'start',
        ];

        $count = $this->getTaskResultDao()->count($conditions);

        return $this->getTaskResultDao()->search($conditions, ['createdTime' => 'DESC'], 0, $count);
    }

    public function findUserFinishedTaskResultsByCourseId($courseId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $conditions = [
            'courseId' => $courseId,
            'userId' => $user['id'],
            'status' => 'finish',
        ];

        $count = $this->countTaskResults($conditions);

        return $this->searchTaskResults($conditions, ['createdTime' => 'DESC'], 0, $count);
    }

    public function countTaskResults($conditions)
    {
        return $this->getTaskResultDao()->count($conditions);
    }

    public function getUserLatestFinishedTaskResultByCourseId($courseId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }
        $conditions = [
            'userId' => $user->getId(),
            'status' => 'finish',
            'courseId' => $courseId,
        ];
        $taskResults = $this->getTaskResultDao()->search($conditions, ['updatedTime' => 'DESC'], 0, 1);

        return array_shift($taskResults);
    }

    public function findUserTaskResultsByTaskIds($taskIds)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin() || empty($taskIds)) {
            return [];
        }

        return $this->getTaskResultDao()->findByTaskIdsAndUserId($taskIds, $user->getId());
    }

    public function countUsersByTaskIdAndLearnStatus($taskId, $status)
    {
        if ('all' === $status) {
            $status = null;
        }
        $task = $this->getTaskService()->getTask($taskId);

        $courseMemberIds = $this->getCourseMemberService()->findMemberUserIdsByCourseId($task['courseId']);

        return $this->getTaskResultDao()->count(['userIds' => $courseMemberIds, 'courseTaskId' => $taskId, 'status' => $status]);
    }

    /**
     * 统计某个任务的学习次数，学习的定义为task_result的status为start、finish，不对用户去重；.
     */
    public function countLearnNumByTaskId($taskId)
    {
        return $this->getTaskResultDao()->countLearnNumByTaskId($taskId);
    }

    public function searchTaskResults($conditions, $orderbys, $start, $limit, $columns = [])
    {
        return $this->getTaskResultDao()->search($conditions, $orderbys, $start, $limit, $columns);
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

    public function getWatchTimeByActivityIdAndUserId($activityId, $userId)
    {
        $result = $this->getTaskResultDao()->getByActivityIdAndUserId($activityId, $userId);
        if (empty($result)) {
            return 0;
        }

        return $result['watchTime'];
    }

    public function getMyLearnedTimeByActivityId($activityId)
    {
        $user = $this->getCurrentUser();
        if (null === $user || !$user->isLogin()) {
            return 0;
        }

        $result = $this->getTaskResultDao()->getByActivityIdAndUserId($activityId, $user['id']);
        if (empty($result)) {
            return 0;
        }

        return $result['time'];
    }

    public function countFinishedTasksByUserIdAndCourseIdsGroupByCourseId($userId, $courseIds)
    {
        return $this->getTaskResultDao()->countFinishedTasksByUserIdAndCourseIdsGroupByCourseId($userId, $courseIds);
    }

    public function countFinishedCompulsoryTasksByUserIdAndCourseId($userId, $courseId)
    {
        return $this->getTaskResultDao()->countFinishedCompulsoryTasksByUserIdAndCourseId($userId, $courseId);
    }

    public function countFinishedCompulsoryTasksByUserIdAndCourseIds($userId, array $courseIds)
    {
        return $this->getTaskResultDao()->countFinishedCompulsoryTasksByUserIdAndCourseIds($userId, $courseIds);
    }

    public function countFinishedCompulsoryTaskNumGroupByUserId($courseId)
    {
        return ArrayToolkit::index($this->getTaskResultDao()->countFinishedCompulsoryTaskNumGroupByUserId($courseId), 'userId');
    }

    public function findTaskresultsByTaskId($taskId)
    {
        return $this->getTaskResultDao()->findTaskresultsByTaskId($taskId);
    }

    public function findTaskResultsByUserId($userId)
    {
        return $this->getTaskResultDao()->findByUserId($userId);
    }

    public function getTaskResultByTaskIdAndUserId($taskId, $userId)
    {
        return $this->getTaskResultDao()->getByTaskIdAndUserId($taskId, $userId);
    }

    public function sumCourseSetLearnedTimeByTaskIds($taskIds)
    {
        return $this->getTaskResultDao()->sumCourseSetLearnedTimeByTaskIds($taskIds);
    }

    public function batchUpdate($ids, $taskResults)
    {
        if (empty($taskResults)) {
            return [];
        }

        return $this->getTaskResultDao()->batchUpdate($ids, $taskResults);
    }

    public function countUserNumByCourseTaskId($conditions)
    {
        return $this->getTaskResultDao()->countUserNumByCourseTaskId($conditions);
    }

    /**
     * @return TaskResultDao
     */
    protected function getTaskResultDao()
    {
        return $this->createDao('Task:TaskResultDao');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
