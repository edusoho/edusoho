<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Assessment\AssessmentFilter;

class ItemBankExerciseAssessmentExerciseRecord extends AbstractResource
{
    public function add(ApiRequest $request, $exerciseId)
    {
        $user = $this->getCurrentUser();

        $assessmentExerciseRecord = $this->getItemBankAssessmentExerciseService()->startAnswer(
            $request->request->get('moduleId', ''),
            $request->request->get('assessmentId', ''),
            $user['id']
        );
        $answerRecord = $this->getAnswerRecordService()->get($assessmentExerciseRecord['answerRecordId']);

        $assessment = $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']);
        $assessmentFilter = new AssessmentFilter();
        $assessmentFilter->filter($assessment);

        return [
            'assessment' => $assessment,
            'assessment_response' => $this->getAnswerService()->getAssessmentResponseByAnswerRecordId($answerRecord['id']),
            'answer_scene' => $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']),
            'answer_record' => $answerRecord,
        ];
    }

    /**
     * @return \Biz\ItemBankExercise\Service\AssessmentExerciseService
     */
    protected function getItemBankAssessmentExerciseService()
    {
        return $this->service('ItemBankExercise:AssessmentExerciseService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
     */
    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerService;
     */
    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }
}
