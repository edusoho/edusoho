<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;

class MeItemBankExerciseModuleAssessment extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\ItemBankExercise\ItemBankExerciseModuleAssessmentFilter", mode="public")
     */
    public function search(ApiRequest $request, $exerciseId, $moduleId)
    {
        $user = $this->getCurrentUser();

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = ['exerciseId' => $exerciseId, 'moduleId' => $moduleId];
        $exerciseAssessments = $this->getItemBankAssessmentExerciseService()->search($conditions, [], $offset, $limit);
        $exerciseAssessments = ArrayToolkit::index($exerciseAssessments, 'id');

        $assessments = $this->getAssessmentService()->findAssessmentsByIds(ArrayToolkit::column($exerciseAssessments, 'assessmentId'));
        $records = $this->getItemBankAssessmentExerciseRecordService()->search(
            ['userId' => $user['id'], 'moduleId' => $moduleId, 'exerciseId' => $exerciseId],
            [],
            0,
            PHP_INT_MAX
        );

        $answerRecords = $this->getAnswerRecordService()->findByIds(array_column($records, 'answerRecordId'));
        $answerAssessments = $this->getAssessmentService()->findAssessmentsByIds(array_column($answerRecords, 'assessment_id'));
        foreach ($records as &$record) {
            $record['assessment'] = $answerAssessments[$answerRecords[$record['answerRecordId']]['assessment_id']];
        }
        $records = ArrayToolkit::index($records, 'assessmentExerciseId');
        $answerRecordGroups = ArrayToolkit::group($records, 'assessmentExerciseId');

        foreach ($exerciseAssessments as &$exerciseAssessment) {
            if (empty($records[$exerciseAssessment['id']]['assessment'])) {
                $exerciseAssessment['assessment'] = $assessments[$exerciseAssessment['assessmentId']];
            } else {
                $exerciseAssessment['assessment'] = $records[$exerciseAssessment['id']]['assessment'];
            }

            if (!empty($records[$exerciseAssessment['id']]['assessment'])) {
                $exerciseAssessment['latestAnswerRecord'] = end($answerRecordGroups[$exerciseAssessment['id']]);
            }
        }

        $total = $this->getItemBankAssessmentExerciseService()->count($conditions);

        return $this->makePagingObject(array_values($exerciseAssessments), $total, $offset, $limit);
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

    /**
     * @return \Biz\ItemBankExercise\Service\AssessmentExerciseRecordService
     */
    protected function getItemBankAssessmentExerciseRecordService()
    {
        return $this->service('ItemBankExercise:AssessmentExerciseRecordService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }
}
