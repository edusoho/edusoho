<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;

class AnswerRecordComment extends AbstractResource
{
    public function add(ApiRequest $request, $recordId)
    {
        $answerRecord = $this->getAnswerRecordService()->get($recordId);
        if (empty($answerRecord)) {
            return (object) [];
        }
        $comment = $request->request->get('comment', '');
        $answerReport = $this->getAnswerReportService()->update($answerRecord['answer_report_id'], ['comment' => $comment]);
        $this->dispatchEvent('answer.comment.update', new Event($answerReport));
        $this->notify($answerRecord);
        $this->getLogService()->info('course', 'answer-record', "修改评语", ['answerRecord'=> $answerRecord, 'userId' => $this->getCurrentUser()->getId()]);
        return $answerReport;
    }

    protected function notify($answerRecord)
    {
        $user = $this->getCurrentUser();
        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerRecord['answer_scene_id']);
        $message = [
            'id' => $answerRecord['id'],
            'courseId' => $activity['fromCourseId'],
            'name' => $activity['title'],
            'userId' => $user['id'],
            'userName' => $user['nickname'],
            'type' => $activity['mediaType'],
            'mode' => 'update'
        ];
        $this->getNotificationService()->notify($answerRecord['user_id'], 'answer-comment', $message);
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    public function getNotificationService()
    {
        return $this->service('User:NotificationService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->service('System:LogService');
    }

    /**
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->service('ItemBank:Answer:AnswerReportService');
    }

    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->service('Activity:TestpaperActivityService');
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}