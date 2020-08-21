<?php

namespace Tests\Unit\ItemBankExercise\Service;

use Biz\BaseTestCase;

class AssessmentExerciseRecordServiceTest extends BaseTestCase
{
    public function testCreate()
    {
        $record = $this->getItemBankAssessmentExerciseRecordService()->create([
            'exerciseId' => 1,
            'moduleId' => 1,
            'assessmentId' => 1,
            'userId' => 1,
            'answerRecordId' => 1,
        ]);

        $this->assertEquals($record['exerciseId'], 1);
        $this->assertEquals($record['moduleId'], 1);
        $this->assertEquals($record['assessmentId'], 1);
        $this->assertEquals($record['userId'], 1);
        $this->assertEquals($record['answerRecordId'], 1);
    }

    public function testUpdate()
    {
        $this->mockAssessmentExerciseRecord();

        $record = $this->getItemBankAssessmentExerciseRecordService()->update(1, ['status' => 'finished']);

        $this->assertEquals($record['status'], 'finished');
    }

    public function testSearch()
    {
        $this->mockAssessmentExerciseRecord();

        $records = $this->getItemBankAssessmentExerciseRecordService()->search(['userId' => 1], [], 0, 3);

        $this->assertEquals(count($records), 2);
    }

    public function testCount()
    {
        $this->mockAssessmentExerciseRecord();

        $count = $this->getItemBankAssessmentExerciseRecordService()->count(['userId' => 1]);

        $this->assertEquals($count, 2);
    }

    public function testGetByAnswerRecordId()
    {
        $this->mockAssessmentExerciseRecord();

        $record = $this->getItemBankAssessmentExerciseRecordService()->getByAnswerRecordId(1);

        $this->assertEquals($record['answerRecordId'], 1);
    }

    public function testGetLatestRecord()
    {
        $this->mockAssessmentExerciseRecord();

        $record = $this->getItemBankAssessmentExerciseRecordService()->getLatestRecord(1, 1, 1);

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

    protected function getItemBankAssessmentExerciseRecordService()
    {
        return $this->biz->service('ItemBankExercise:AssessmentExerciseRecordService');
    }

    protected function getItemBankAssessmentExerciseRecordDao()
    {
        return $this->biz->dao('ItemBankExercise:AssessmentExerciseRecordDao');
    }
}
