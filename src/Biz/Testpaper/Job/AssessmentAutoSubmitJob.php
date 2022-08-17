<?php

namespace Biz\Testpaper\Job;

use Biz\System\Service\LogService;
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
            if (empty($record) || $record['status'] != 'doing') {
                return;
            }
            $this->getAnswerService()->submitAnswer($this->getAnswerService()->buildAssessmentResponse($record['id']));
            $this->getLogService()->info('assessment', 'auto_submit_answers', "{$user['nickname']}({$user['id']})的答题(记录id:{$record['id']})自动提交", ['recordId' => $record['id']]);
        } catch (\Exception $e) {
            $this->getLogService()->error('assessment', 'auto_submit_answers_error', "{$user['nickname']}({$user['id']})的答题(记录id:{$record['id']})自动提交失败", $e->getMessage());
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
}