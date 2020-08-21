<?php

namespace Tests\Unit\ItemBankExercise\Service;

use Biz\BaseTestCase;

class ChapterExerciseServiceTest extends BaseTestCase
{
    public function testStartAnswer()
    {
        $this->mockItemBankExerciseService();
        $this->mockItemBankExerciseModuleService();
        $this->mockItemCategoryService();
        $this->mockQuestionBankService();
        $this->mockItemService();
        $this->mockAssessmentService();
        $this->mockAnswerService();

        $answerRecord = $this->getItemBankChapterExerciseService()->startAnswer(1, 1, 1);

        $this->assertEquals($answerRecord['id'], 1);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionCode 5000305
     */
    public function testStartAnswer_whenModuleMiss_thenThrowCommonException()
    {
        $answerRecord = $this->getItemBankChapterExerciseService()->startAnswer(1, 1, 1);
    }

    /**
     * @expectedException \Biz\ItemBankExercise\ItemBankExerciseException
     * @expectedExceptionCode 4037501
     */
    public function testStartAnswer_whenCannotLearningExercise_thenThrowItemBankExerciseException()
    {
        $this->mockItemBankExerciseModuleService();

        $answerRecord = $this->getItemBankChapterExerciseService()->startAnswer(1, 1, 1);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionCode 5000305
     */
    public function testStartAnswer_whenItemCategoryQuestionNumEq0_thenThrowCommonException()
    {
        $this->mockItemBankExerciseModuleService();
        $this->mockItemBankExerciseService();
        $answerRecord = $this->getItemBankChapterExerciseService()->startAnswer(1, 1, 1);
    }

    /**
     * @expectedException \Biz\ItemBankExercise\ItemBankExerciseException
     * @expectedExceptionCode 5007512
     */
    public function testStartAnswer_whenChapterEnableFalse_thenThrowItemBankExerciseException()
    {
        $this->mockItemBankExerciseModuleService();
        $this->mockItemCategoryService();
        $this->mockBiz(
            'ItemBankExercise:ExerciseService',
            [
                [
                    'functionName' => 'canLearnExercise',
                    'returnValue' => ['code' => 'success'],
                ],
                [
                    'functionName' => 'get',
                    'returnValue' => ['chapterEnable' => 0],
                ],
            ]
        );

        $answerRecord = $this->getItemBankChapterExerciseService()->startAnswer(1, 1, 1);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionCode 5000305
     */
    public function testStartAnswer_whenCategoryIdMiss_thenThrowCommonException()
    {
        $this->mockItemBankExerciseModuleService();
        $this->mockItemBankExerciseService();
        $this->mockItemCategoryService();
        $answerRecord = $this->getItemBankChapterExerciseService()->startAnswer(1, 1, 1);
    }

    /**
     * @expectedException \Biz\ItemBankExercise\ItemBankExerciseException
     * @expectedExceptionCode 5007502
     */
    public function testStartAnswer_whenLatestRecordIsDoing_thenThrowItemBankExerciseException()
    {
        $this->mockItemBankExerciseService();
        $this->mockItemBankExerciseModuleService();
        $this->mockItemCategoryService();
        $this->mockQuestionBankService();
        $this->mockBiz(
            'ItemBankExercise:ChapterExerciseRecordService',
            [
                [
                    'functionName' => 'getLatestRecord',
                    'returnValue' => [
                        'status' => 'doing',
                    ],
                ],
            ]
        );

        $answerRecord = $this->getItemBankChapterExerciseService()->startAnswer(1, 1, 1);
    }

    protected function mockItemBankExerciseService()
    {
        $this->mockBiz(
            'ItemBankExercise:ExerciseService',
            [
                [
                    'functionName' => 'canLearnExercise',
                    'returnValue' => ['code' => 'success'],
                ],
                [
                    'functionName' => 'get',
                    'returnValue' => ['chapterEnable' => 1, 'questionBankId' => 1],
                ],
            ]
        );
    }

    protected function mockItemBankExerciseModuleService()
    {
        $this->mockBiz(
            'ItemBankExercise:ExerciseModuleService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => [
                        'id' => 1,
                        'exerciseId' => 1,
                        'title' => '章节练习',
                        'type' => 'chapter',
                        'answerSceneId' => 1,
                    ],
                ],
            ]
        );
    }

    protected function mockItemCategoryService()
    {
        $this->mockBiz(
            'ItemBank:Item:ItemCategoryService',
            [
                [
                    'functionName' => 'getItemCategory',
                    'returnValue' => [
                        'question_num' => 2,
                        'bank_id' => 1,
                        'parent_id' => 0,
                        'name' => '第一章',
                    ],
                ],
            ]
        );
    }

    protected function mockQuestionBankService()
    {
        $this->mockBiz(
            'QuestionBank:QuestionBankService',
            [
                [
                    'functionName' => 'getQuestionBank',
                    'returnValue' => [
                        'itemBank' => [
                            'id' => 1,
                        ],
                    ],
                ],
            ]
        );
    }

    protected function mockItemService()
    {
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            [
                [
                    'functionName' => 'searchItems',
                    'returnValue' => [
                        'itemBank' => [
                            'id' => 1,
                        ],
                    ],
                ],
                [
                    'functionName' => 'findItemsByIds',
                    'returnValue' => [
                        'itemBank' => [
                            'id' => 1,
                        ],
                    ],
                ],
            ]
        );
    }

    protected function mockAssessmentService()
    {
        $this->mockBiz(
            'ItemBank:Assessment:AssessmentService',
            [
                [
                    'functionName' => 'createAssessment',
                    'returnValue' => [
                        'id' => 1,
                        'question_count' => 2,
                    ],
                ],
                [
                    'functionName' => 'openAssessment',
                    'returnValue' => [
                        'id' => 1,
                        'question_count' => 2,
                    ],
                ],
            ]
        );
    }

    protected function mockAnswerService()
    {
        $this->mockBiz(
            'ItemBank:Answer:AnswerService',
            [
                [
                    'functionName' => 'startAnswer',
                    'returnValue' => [
                        'id' => 1,
                    ],
                ],
            ]
        );
    }

    protected function getItemBankChapterExerciseService()
    {
        return $this->biz->service('ItemBankExercise:ChapterExerciseService');
    }
}
