<?php

namespace Biz\Testpaper\Job;

use Biz\Course\Service\MemberService;
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
        $activity = $this->getActivityService()->getByMediaIdAndMediaType($testpaperActivity['id'], 'testpaper');
        if (empty($activity)) {
            return;
        }

        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);
        if ('published' != $task['status']) {
            return;
        }

        $answerRecords = $this->getAnswerRecordService()->findByAnswerSceneId($answerScene['id']);
        $members = $this->getCourseMemberService()->searchMembers(
            [
                'courseId' => $activity['fromCourseId'],
                'excludeUserIds' => array_column($answerRecords, 'user_id'),
                'role' => 'student',
            ],
            ['createdTime' => 'DESC'],
            0,
            1000,
            ['userId']
        );
        if (empty($members)) {
            return;
        }

        $this->getAnswerService()->batchAutoSubmit($answerScene['id'], $testpaperActivity['mediaId'], array_column($members, 'userId'));

        $this->getLogService()->info('answer', 'create', '提交成功');

        $this->getSchedulerService()->register([
            'name' => 'noAnswerAssessmentAutoSubmitJob_'.$answerScene['id'],
            'expression' => time(),
            'class' => 'Biz\Testpaper\Job\NoAnswerAssessmentAutoSubmitJob',
            'misfire_threshold' => 60 * 10,
            'misfire_policy' => 'executing',
            'args' => ['answerSceneId' => $answerScene['id']],
        ]);

        $this->getLogService()->info('job', 'register', "定时任务noAnswerAssessmentAutoSubmitJob_(#{$answerScene['id']})创建成功");
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
}
