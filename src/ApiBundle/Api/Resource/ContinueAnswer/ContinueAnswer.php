<?php

namespace ApiBundle\Api\Resource\ContinueAnswer;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Activity\ActivityFilter;
use ApiBundle\Api\Resource\Assessment\AssessmentFilter;
use ApiBundle\Api\Resource\Assessment\AssessmentResponseFilter;
use Biz\Activity\ActivityException;
use Biz\Activity\Service\ActivityService;
use Biz\Common\CommonException;
use Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException;

class ContinueAnswer extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $answerRecord = $this->getAnswerRecordService()->get($request->request->get('answer_record_id'));
        if (empty($answerRecord) || $this->getCurrentUser()['id'] != $answerRecord['user_id']) {
            throw CommonException::ERROR_PARAMETER();
        }

        $answerRecord = $this->getAnswerService()->continueAnswer($request->request->get('answer_record_id'));

        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerRecord['answer_scene_id']);
        $activityFilter = new ActivityFilter();
        $activityFilter->filter($activity);

        $user = $this->getCurrentUser();
        $activity['isOnlyStudent'] = $user['roles'] == ["ROLE_USER"];

        $assessment = $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']);
        if (empty($assessment)) {
            throw AssessmentException::ASSESSMENT_NOTEXIST();
        }
        if ('open' !== $assessment['status']) {
            throw AssessmentException::ASSESSMENT_NOTOPEN();
        }

        $assessmentFilter = new AssessmentFilter();
        $assessmentFilter->filter($assessment);
        $this->removeAnalysisAndAnswer($assessment);

        $assessmentResponse = $this->getAnswerService()->getAssessmentResponseByAnswerRecordId($answerRecord['id']);
        $assessmentResponseFilter = new AssessmentResponseFilter();
        $assessmentResponseFilter->filter($assessmentResponse);

        return [
            'assessment' => $assessment,
            'assessment_response' => $assessmentResponse,
            'answer_scene' => $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']),
            'answer_record' => $answerRecord,
            'metaActivity'=> empty($activity) ? (object)[] : $activity,
        ];
    }

    private function removeAnalysisAndAnswer(&$assessment) {
        foreach ($assessment['sections'] as &$section){
            foreach ($section['items'] as &$item){
                foreach ($item['questions'] as &$question){
                    $question['analysis'] = "";
                    $question['answer'] = [];
                }
            }
        }
    }

    protected function getAnswerActivityService()
    {
        return $this->service('AnswerActivity:AnswerActivityService');
    }

    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
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

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
