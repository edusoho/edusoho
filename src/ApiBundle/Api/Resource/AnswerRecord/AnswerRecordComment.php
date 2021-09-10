<?php


namespace ApiBundle\Api\Resource\AnswerRecord;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\Activity\Service\ActivityService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Codeages\Biz\Framework\Event\Event;

class AnswerRecordComment extends AbstractResource
{
    public function add(ApiRequest $request, $id)
    {
        $answerRecord = $this->getAnswerRecordService()->get($id);
        if (empty($answerRecord)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerRecord['answer_scene_id']);
        $this->getCourseService()->tryManageCourse($activity['fromCourseId']);

        $answerReport = $this->getAnswerReportService()->get($answerRecord['answer_report_id']);
        $report = $this->getAnswerReportService()->update($answerReport['id'], ['comment' => $request->request->get('comment')]);
        $this->dispatchEvent('answer.comment.update', new Event($report));

        return ['success' => true];
    }

    protected function getAnswerReportService()
    {
        return $this->service('ItemBank:Answer:AnswerReportService');
    }

    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}