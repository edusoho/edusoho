<?php

namespace Biz\Testpaper\Job;

use Biz\Activity\Constant\ActivityMediaType;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;

class NoAnswerAssessmentAutoSubmitJob extends AbstractJob
{
    public function execute()
    {
        $params = $this->args;
        $answerScene = $this->getAnswerSceneService()->get($params['answerSceneId']);
        if (empty($answerScene['end_time'])) {
            return;
        }
        $testpaperActivity = $this->getTestpaperActivityService()->getActivityByAnswerSceneId($answerScene['id']);
        if (empty($testpaperActivity)) {
            return;
        }
        $activity = $this->getActivityService()->getByMediaIdAndMediaType($testpaperActivity['id'], ActivityMediaType::TESTPAPER);
        if (empty($activity)) {
            return;
        }

        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);
        if ('published' != $task['status']) {
            return;
        }

        $userIds = $this->findNeedSubmitUserIds($answerScene['id'], $activity['fromCourseId'], 1000);
        if (empty($userIds)) {
            return;
        }

        try {
            $this->getAnswerService()->batchAutoSubmit($answerScene['id'], $testpaperActivity['mediaId'], $userIds);

            $this->getLogService()->info('assessment', 'auto_submit_answers', "用户自动交卷成功,答题场次为{$answerScene['id']}");

            $this->getSchedulerService()->register([
                'name' => 'noAnswerAssessmentAutoSubmitJob_'.$answerScene['id'],
                'expression' => time(),
                'class' => 'Biz\Testpaper\Job\NoAnswerAssessmentAutoSubmitJob',
                'misfire_threshold' => 60 * 10,
                'misfire_policy' => 'executing',
                'args' => ['answerSceneId' => $answerScene['id']],
            ]);
        } catch (\Exception $e) {
            $this->getLogService()->error('assessment', 'auto_submit_answers_error', "用户自动交卷失败,答题场次为{$answerScene['id']}", $e->getMessage());
        }
    }

    private function findNeedSubmitUserIds($answerSceneId, $courseId, $limit)
    {
        $answerRecords = $this->getAnswerRecordService()->findByAnswerSceneId($answerSceneId);
        $excludeUserIds = array_column($answerRecords, 'user_id');
        $courseMembers = $this->getCourseMemberService()->searchMembers(
            [
                'courseId' => $courseId,
                'excludeUserIds' => $excludeUserIds,
                'role' => 'student',
            ],
            ['createdTime' => 'DESC'],
            0,
            $limit,
            ['userId']
        );
        $userIds = array_column($courseMembers, 'userId');

        $classroom = $this->getClassroomService()->getClassroomByCourseId($courseId);
        if (!empty($classroom)) {
            $classroomMembers = $this->getClassroomService()->searchMembers(
                [
                    'classroomId' => $classroom['id'],
                    'excludeUserIds' => array_merge($userIds, $excludeUserIds),
                    'role' => 'student',
                ],
                ['createdTime' => 'DESC'],
                0,
                $limit,
                ['userId']
            );
            $userIds = array_merge($userIds, array_column($classroomMembers, 'userId'));
        }

        return $userIds;
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerSceneService');
    }

    protected function getTestpaperActivityService()
    {
        return $this->biz->service('Activity:TestpaperActivityService');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerService');
    }

    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return AnswerRecordService
     */
    public function getAnswerRecordService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}
