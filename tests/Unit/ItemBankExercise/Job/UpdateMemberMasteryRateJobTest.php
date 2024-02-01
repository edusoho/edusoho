<?php

namespace Tests\Unit\ItemBankExercise;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\Job\UpdateMemberMasteryRateJob;

class UpdateMemberMasteryRateJobTest extends BaseTestCase
{
    public function testExcute()
    {
        $this->getItemBankExerciseMemberDao()->batchCreate(
            [
                [
                    'exerciseId' => 1,
                    'userId' => 1,
                    'remark' => '',
                ],
                [
                    'exerciseId' => 1,
                    'userId' => 2,
                    'remark' => '',
                ],
                [
                    'exerciseId' => 1,
                    'userId' => 3,
                    'remark' => '',
                ],
            ]
        );

        $this->getItemBankExerciseQuestionRecordDao()->batchCreate(
            [
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
                    'status' => 'wrong',
                ],
                [
                    'exerciseId' => 1,
                    'answerRecordId' => 1,
                    'itemId' => 1,
                    'questionId' => 1,
                    'userId' => 2,
                    'status' => 'right',
                ],
                [
                    'exerciseId' => 1,
                    'answerRecordId' => 1,
                    'itemId' => 1,
                    'questionId' => 1,
                    'userId' => 2,
                    'status' => 'wrong',
                ],
                [
                    'exerciseId' => 1,
                    'answerRecordId' => 1,
                    'itemId' => 1,
                    'questionId' => 1,
                    'userId' => 3,
                    'status' => 'right',
                ],
            ]
        );

        $this->mockBiz(
            'QuestionBank:QuestionBankService',
            [
                [
                    'functionName' => 'getQuestionBank',
                    'returnValue' => [
                        'id' => 1,
                        'itemBank' => [
                            'id' => 1,
                            'question_num' => 10,
                        ],
                    ],
                ],
            ]
        );

        $this->mockBiz(
            'ItemBankExercise:ExerciseService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => [
                        'id' => 1,
                        'questionBankId' => 1,
                        'chapterEnable' => 1,
                        'assessmentEnable' => 0,
                    ],
                ],
            ]
        );

        $this->mockBiz(
            'ItemBankExercise:ExerciseModuleService',
            [
                [
                    'functionName' => 'findByExerciseIdAndType',
                    'returnValue' => [
                        ['id' => 1],
                    ],
                ],
            ]
        );

        $this->mockBiz(
            'ItemBankExercise:ChapterExerciseService',
            [
                [
                    'functionName' => 'getPublishChapterTreeList',
                    'runTimes' => 1,
                    'returnValue' => [
                        ['id' => 1],
                    ],
                ],
            ]
        );

        $this->mockBiz(
            'ItemBank:Item:ItemService',
            [
                [
                    'functionName' => 'findItemsByCategoryIds',
                    'runTimes' => 1,
                    'returnValue' => [
                        ['id' => 1, 'question_num' => 5],
                    ],
                ],
            ]
        );

        $job = new UpdateMemberMasteryRateJob(['args' => ['itemBankExericseId' => 1]], $this->biz);
        $job->execute();

        $members = $this->getItemBankExerciseMemberDao()->search(['exerciseId' => 1], [], 0, PHP_INT_MAX);
        $this->assertEquals(2, $members[0]['doneQuestionNum']);
        $this->assertEquals(1, $members[0]['rightQuestionNum']);
        $this->assertEquals(20.0, $members[0]['masteryRate']);
        $this->assertEquals(40.0, $members[0]['completionRate']);
        $this->assertEquals(2, $members[1]['doneQuestionNum']);
        $this->assertEquals(1, $members[1]['rightQuestionNum']);
        $this->assertEquals(20.0, $members[1]['masteryRate']);
        $this->assertEquals(40.0, $members[1]['completionRate']);
        $this->assertEquals(1, $members[2]['doneQuestionNum']);
        $this->assertEquals(1, $members[2]['rightQuestionNum']);
        $this->assertEquals(20.0, $members[2]['masteryRate']);
        $this->assertEquals(20.0, $members[2]['completionRate']);
    }

    protected function getItemBankExerciseQuestionRecordDao()
    {
        return $this->biz->dao('ItemBankExercise:ExerciseQuestionRecordDao');
    }

    protected function getItemBankExerciseMemberDao()
    {
        return $this->biz->dao('ItemBankExercise:ExerciseMemberDao');
    }
}
