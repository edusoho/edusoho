<?php

namespace Biz\Testpaper\Job;

use Biz\Activity\Service\ActivityService;
use Biz\System\Service\LogService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;

class AssessmentAutoSubmitJob extends AbstractJob
{
    public function execute()
    {
        $record = $this->getAnswerRecordService()->get($this->args['answerRecordId']);
        $user = $this->getUserService()->getUser($record['user_id']);
        try {
            if (empty($record) || 'doing' != $record['status']) {
                return;
            }
            $this->getAnswerService()->submitAnswer($this->getAnswerService()->buildAutoSubmitAssessmentResponse($record['id']));

            $this->tryFinishTask($record['answer_scene_id'], $user['id']);

            $this->getLogService()->info('assessment', 'auto_submit_answers', "{$user['nickname']}({$user['id']})的答题(记录id:{$record['id']})自动提交", ['recordId' => $record['id']]);
        } catch (\Exception $e) {
            $this->getLogService()->error('assessment', 'auto_submit_answers_error', "{$user['nickname']}({$user['id']})的答题(记录id:{$record['id']})自动提交失败", $e->getMessage());
        }
    }

    protected function tryFinishTask($answerSceneId, $userId)
    {
        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerSceneId);
        if (empty($activity)) {
            return;
        }
        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);
        if (empty($task)) {
            return;
        }
        if ($this->getTaskService()->isFinished($task['id'], $userId)) {
            $this->getTaskService()->finishTaskResult($task['id'], $userId);
        }
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }
}
