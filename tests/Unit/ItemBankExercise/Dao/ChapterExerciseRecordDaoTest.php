<?php

namespace Tests\Unit\ItemBankExercise\Dao;

use Biz\BaseTestCase;

class ChapterExerciseRecordDaoTest extends BaseTestCase
{
    public function testGetByAnswerRecordId()
    {
        $this->mockChapterExerciseRecord();

        $record = $this->getItemBankChapterExerciseRecordDao()->getByAnswerRecordId(1);

        $this->assertEquals($record['answerRecordId'], 1);
    }

    public function testGetLatestRecord()
    {
        $this->mockChapterExerciseRecord();

        $record = $this->getItemBankChapterExerciseRecordDao()->getLatestRecord(1, 1, 1);

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

    protected function getItemBankChapterExerciseRecordDao()
    {
        return $this->biz->dao('ItemBankExercise:ChapterExerciseRecordDao');
    }
}
