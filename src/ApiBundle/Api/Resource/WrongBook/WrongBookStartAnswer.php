<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Assessment\AssessmentFilter;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class WrongBookStartAnswer extends AbstractResource
{
    /**
     * @param $poolId
     */
    public function add(ApiRequest $request, $poolId)
    {
        $assessment = [
            'name' => '',
            'displayable' => 0,
            'description' => '',
            'bank_id' => 0,
            'sections' => [],
        ];

        $assessment = $this->getAssessmentService()->createAssessment($assessment);

        $this->getAssessmentService()->openAssessment($assessment['id']);

        $answerRecord = $this->getAnswerService()->startAnswer($sceneId, $assessment['id'], $this->getCurrentUser()['id']);

        $assessment = $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']);

        if (empty($assessment)) {
            throw AssessmentException::ASSESSMENT_NOTEXIST();
        }
        if ('open' !== $assessment['status']) {
            throw AssessmentException::ASSESSMENT_NOTOPEN();
        }

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
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->getBiz()->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerSceneService');
    }
}
