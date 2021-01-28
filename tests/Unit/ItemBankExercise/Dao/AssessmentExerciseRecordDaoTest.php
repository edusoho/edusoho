<?php

namespace Tests\Unit\ItemBankExercise\Dao;

use Biz\BaseTestCase;

class AssessmentExerciseRecordDaoTest extends BaseTestCase
{
    public function testGetByAnswerRecordId()
    {
        $this->mockAssessmentExerciseRecord();

        $record = $this->getItemBankAssessmentExerciseRecordDao()->getByAnswerRecordId(1);

        $this->assertEquals($record['answerRecordId'], 1);
    }

    public function testGetLatestRecord()
    {
        $this->mockAssessmentExerciseRecord();

        $record = $this->getItemBankAssessmentExerciseRecordDao()->getLatestRecord(1, 1, 1);

        $this->assertEquals($record['id'], 1);
    }

    protected function mockAssessmentExerciseRecord()
    {
        $this->getItemBankAssessmentExerciseRecordDao()->create([
            'id' => 1,
            'exerciseId' => 1,
            'moduleId' => 1,
            'assessmentId' => 1,
            'userId' => 1,
            'answerRecordId' => 1,
        ]);

        $this->getItemBankAssessmentExerciseRecordDao()->create([
            'id' => 2,
            'exerciseId' => 1,
            'moduleId' => 1,
            'assessmentId' => 2,
            'userId' => 1,
            'answerRecordId' => 2,
        ]);
    }

    protected function getItemBankAssessmentExerciseRecordDao()
    {
        return $this->biz->dao('ItemBankExercise:AssessmentExerciseRecordDao');
    }
}
