<?php

namespace Tests\Unit\ItemBankExercise\Service;

use Biz\BaseTestCase;

class ExerciseQuestionRecordServiceTest extends BaseTestCase
{
    public function testUpdateByAnswerRecordIdAndModuleId()
    {
        $this->mockBiz(
            'ItemBank:Answer:AnswerRecordService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => [
                        'id' => 1,
                        'user_id' => 1,
                        'answer_report_id' => 1,
                    ],
                ],
            ]
        );

        $this->mockBiz(
            'ItemBankExercise:ExerciseModuleService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => [
                        'exerciseId' => 1,
                        'type' => 'chapter',
                    ],
                ],
            ]
        );

        $this->mockBiz(
            'ItemBank:Answer:AnswerReportService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => [
                        'right_question_count' => 1,
                        'right_rate' => 50.0,
                        'answer_record_id' => 1,
                        'section_reports' => [
                            [
                                'item_reports' => [
                                    [
                                        'item_id' => 1,
                                        'question_reports' => [
                                            [
                                                'response' => ['A'],
                                                'status' => 'right',
                                                'question_id' => 1,
                                            ],
                                            [
                                                'response' => [],
                                                'status' => 'no_answer',
                                                'question_id' => 2,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->getItemBankExerciseQuestionRecordService()->updateByAnswerRecordIdAndModuleId(1, 1);

        $questionRecords = $this->getItemBankExerciseQuestionRecordService()->findByUserIdAndExerciseId(1, 1);

        $this->assertEquals($questionRecords[0]['questionId'], 1);
        $this->assertEquals($questionRecords[0]['status'], 'right');
    }

    public function testFindByUserIdAndExerciseId()
    {
        $this->mockExerciseQuestionRecord();

        $records = $this->getItemBankExerciseQuestionRecordService()->findByUserIdAndExerciseId(1, 1);

        $this->assertEquals(count($records), 2);
    }

    public function testBatchCreate()
    {
        $this->getItemBankExerciseQuestionRecordService()->batchCreate([
            [
                'exerciseId' => 1,
                'answerRecordId' => 1,
                'itemId' => 1,
                'questionId' => 1,
                'userId' => 1,
                'status' => 'right',
            ],
            [
                'exerciseId' => 1,
                'answerRecordId' => 1,
                'itemId' => 1,
                'questionId' => 1,
                'userId' => 1,
                'status' => 'right',
            ],
        ]);

        $this->assertEquals($this->getItemBankExerciseQuestionRecordDao()->count([]), 2);
    }

    public function testBatchUpdate()
    {
        $this->mockExerciseQuestionRecord();

        $this->getItemBankExerciseQuestionRecordService()->batchUpdate(
            [1, 2],
            [['id' => 1, 'status' => 'wrong'], ['id' => 2, 'status' => 'wrong']]
        );

        $records = $this->getItemBankExerciseQuestionRecordDao()->search([], [], 0, 2);

        $this->assertEquals($records[0]['status'], 'wrong');
        $this->assertEquals($records[1]['status'], 'wrong');
    }

    public function testDeleteByQuestionIds()
    {
        $this->mockExerciseQuestionRecord();

        $this->getItemBankExerciseQuestionRecordService()->deleteByQuestionIds([1]);

        $this->assertEquals($this->getItemBankExerciseQuestionRecordDao()->count([]), 1);
    }

    public function testDeleteByItemIds()
    {
        $this->mockExerciseQuestionRecord();

        $this->getItemBankExerciseQuestionRecordService()->deleteByItemIds([1]);

        $this->assertEquals($this->getItemBankExerciseQuestionRecordDao()->count([]), 0);
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

    protected function getItemBankExerciseQuestionRecordService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseQuestionRecordService');
    }

    protected function getItemBankExerciseQuestionRecordDao()
    {
        return $this->biz->dao('ItemBankExercise:ExerciseQuestionRecordDao');
    }
}
