<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;

class ItemBankExerciseModuleAssessment extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $exerciseId, $moduleId)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = ['exerciseId' => $exerciseId, 'moduleId' => $moduleId];
        $exerciseAssessments = $this->getItemBankAssessmentExerciseService()->search($conditions, [], $offset, $limit);

        $assessments = $this->getAssessmentService()->findAssessmentsByIds(ArrayToolkit::column($exerciseAssessments, 'assessmentId'));
        foreach ($exerciseAssessments as &$exerciseAssessment) {
            $exerciseAssessment['assessment'] = empty($assessments[$exerciseAssessment['assessmentId']]) ? (object) [] : $assessments[$exerciseAssessment['assessmentId']];
        }

        $total = $this->getItemBankAssessmentExerciseService()->count($conditions);

        return $this->makePagingObject($exerciseAssessments, $total, $offset, $limit);
    }

    /**
     * @return \Biz\ItemBankExercise\Service\AssessmentExerciseService
     */
    protected function getItemBankAssessmentExerciseService()
    {
        return $this->service('ItemBankExercise:AssessmentExerciseService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Assessment\Service\AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }
}
