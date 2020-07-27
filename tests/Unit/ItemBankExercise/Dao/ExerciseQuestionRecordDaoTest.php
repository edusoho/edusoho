<?php

namespace Tests\Unit\ItemBankExercise\Dao;

use Biz\BaseTestCase;

class ExerciseQuestionRecordDaoTest extends BaseTestCase
{
    public function testFindByUserIdAndExerciseId()
    {
        $this->mockExerciseQuestionRecord();

        $records = $this->getItemBankExerciseQuestionRecordDao()->findByUserIdAndExerciseId(1, 1);

        $this->assertEquals(count($records), 2);
    }

    protected function mockExerciseQuestionRecord()
    {
        $this->getItemBankExerciseQuestionRecordDao()->create([
            'id' => 1,
            'exerciseId' => 1,
            'answerRecordId' => 1,
            'itemId' => 1,
            'questionId' => 1,
            'userId' => 1,
            'status' => 'right',
        ]);

        $this->getItemBankExerciseQuestionRecordDao()->create([
            'id' => 2,
            'exerciseId' => 1,
            'answerRecordId' => 1,
            'itemId' => 1,
            'questionId' => 2,
            'userId' => 1,
            'status' => 'wrong',
        ]);
    }

    protected function getItemBankExerciseQuestionRecordDao()
    {
        return $this->biz->dao('ItemBankExercise:ExerciseQuestionRecordDao');
    }
}
