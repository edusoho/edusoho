<?php

namespace Biz\ItemBankExercise\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\ItemBankExercise\Service\AssessmentExerciseRecordService;

class AssessmentExerciseRecordServiceImpl extends BaseService implements AssessmentExerciseRecordService
{
    public function search($conditions, $sort, $start, $limit, $columns = [])
    {
        return $this->getItemBankAssessmentExerciseRecordDao()->search($conditions, $sort, $start, $limit, $columns);
    }

    public function count($conditions)
    {
        return $this->getItemBankAssessmentExerciseRecordDao()->search($conditions);
    }

    public function create($assessmentExerciseRecord)
    {
        $assessmentExerciseRecord = ArrayToolkit::parts($assessmentExerciseRecord, ['exerciseId', 'moduleId', 'assessmentId', 'userId', 'answerRecordId']);

        return $this->getItemBankassessmentExerciseRecordDao()->create($assessmentExerciseRecord);
    }

    public function update($id, $assessmentExerciseRecord)
    {
        $assessmentExerciseRecord = ArrayToolkit::parts($assessmentExerciseRecord, ['status']);

        return $this->getItemBankAssessmentExerciseRecordDao()->update($id, $assessmentExerciseRecord);
    }

    public function getByAnswerRecordId($answerRecordId)
    {
        return $this->getItemBankAssessmentExerciseRecordDao()->getByAnswerRecordId($answerRecordId);
    }

    public function getLatestRecord($moduleId, $assessmentId, $userId)
    {
        return $this->getItemBankAssessmentExerciseRecordDao()->getLatestRecord($moduleId, $assessmentId, $userId);
    }

    protected function getItemBankAssessmentExerciseRecordDao()
    {
        return $this->createDao('ItemBankExercise:AssessmentExerciseRecordDao');
    }
}
