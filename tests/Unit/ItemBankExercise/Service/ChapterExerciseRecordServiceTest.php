<?php

namespace Tests\Unit\ItemBankExercise\Service;

use Biz\BaseTestCase;

class ChapterExerciseRecordServiceTest extends BaseTestCase
{
    public function testCreate()
    {
        $record = $this->getItemBankChapterExerciseRecordService()->create([
            'moduleId' => 1,
            'exerciseId' => 1,
            'itemCategoryId' => 1,
            'userId' => 1,
            'answerRecordId' => 1,
            'questionNum' => 1,
        ]);

        $this->assertEquals($record['moduleId'], 1);
        $this->assertEquals($record['exerciseId'], 1);
        $this->assertEquals($record['itemCategoryId'], 1);
        $this->assertEquals($record['userId'], 1);
        $this->assertEquals($record['answerRecordId'], 1);
        $this->assertEquals($record['questionNum'], 1);
    }

    public function search()
    {
        $this->mockChapterExerciseRecord();

        $records = $this->getItemBankChapterExerciseRecordService()->search(['moduleId' => 1], [], 0, 2);

        $this->assertEquals(count($records), 2);
    }

    public function testCount()
    {
        $this->mockChapterExerciseRecord();

        $count = $this->getItemBankChapterExerciseRecordService()->count(['moduleId' => 1]);

        $this->assertEquals($count, 2);
    }

    public function testGet()
    {
        $this->mockChapterExerciseRecord();

        $record = $this->getItemBankChapterExerciseRecordService()->get(1);

        $this->assertEquals($record['id'], 1);
    }

    public function testGetByAnswerRecordId()
    {
        $this->mockChapterExerciseRecord();

        $record = $this->getItemBankChapterExerciseRecordService()->getByAnswerRecordId(1);

        $this->assertEquals($record['answerRecordId'], 1);
    }

    public function testUpdate()
    {
        $this->mockChapterExerciseRecord();

        $record = $this->getItemBankChapterExerciseRecordService()->update(1, [
            'status' => 'finished',
            'doneQuestionNum' => 2,
            'rightQuestionNum' => 2,
            'rightRate' => 20.1,
        ]);

        $this->assertEquals($record['status'], 'finished');
        $this->assertEquals($record['doneQuestionNum'], 2);
        $this->assertEquals($record['rightQuestionNum'], 2);
        $this->assertEquals($record['rightRate'], 20.1);
    }

    public function testGetLatestRecord()
    {
        $this->mockChapterExerciseRecord();

        $record = $this->getItemBankChapterExerciseRecordService()->getLatestRecord(1, 1, 1);

        $this->assertEquals($record['answerRecordId'], 1);
    }

    protected function mockChapterExerciseRecord()
    {
        $this->getItemBankChapterExerciseRecordDao()->create([
            'id' => 1,
            'moduleId' => 1,
            'exerciseId' => 1,
            'itemCategoryId' => 1,
            'userId' => 1,
            'status' => 'doing',
            'answerRecordId' => 1,
            'questionNum' => 1,
            'doneQuestionNum' => 0,
            'rightQuestionNum' => 0,
            'rightRate' => 0,
        ]);

        $this->getItemBankChapterExerciseRecordDao()->create([
            'id' => 2,
            'moduleId' => 1,
            'exerciseId' => 1,
            'itemCategoryId' => 2,
            'userId' => 1,
            'status' => 'doing',
            'answerRecordId' => 2,
            'questionNum' => 2,
            'doneQuestionNum' => 0,
            'rightQuestionNum' => 0,
            'rightRate' => 0,
        ]);
    }

    protected function getItemBankChapterExerciseRecordService()
    {
        return $this->biz->service('ItemBankExercise:ChapterExerciseRecordService');
    }

    protected function getItemBankChapterExerciseRecordDao()
    {
        return $this->biz->dao('ItemBankExercise:ChapterExerciseRecordDao');
    }
}
