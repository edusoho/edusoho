<?php

namespace Tests\Unit\ItemBankExercise\Service;

use Biz\BaseTestCase;

class AssessmentExerciseServiceTest extends BaseTestCase
{
    public function testSearch()
    {
        $this->mockAssessmentExercise();

        $assessmentExercises = $this->getItemBankAssessmentExerciseService()->search([
            'exerciseId' => 1,
            'moduleId' => 1,
            'assessmentId' => 1,
        ], [], 0, 1);

        $this->assertEquals($assessmentExercises[0]['exerciseId'], 1);
        $this->assertEquals($assessmentExercises[0]['moduleId'], 1);
        $this->assertEquals($assessmentExercises[0]['assessmentId'], 1);
    }

    public function testCount()
    {
        $this->mockAssessmentExercise();

        $count = $this->getItemBankAssessmentExerciseService()->count([]);

        $this->assertEquals($count, 1);
    }

    public function testGetByModuleIdAndAssessmentId()
    {
        $this->mockAssessmentExercise();

        $assessmentExercise = $this->getItemBankAssessmentExerciseService()->getByModuleIdAndAssessmentId(1, 1);

        $this->assertEquals($assessmentExercise['moduleId'], 1);
        $this->assertEquals($assessmentExercise['assessmentId'], 1);
    }

    public function testStartAnswer()
    {
        $this->mockAssessmentExercise();
        $this->mockItemBankExerciseModuleService();
        $this->mockAnswerService();
        $this->mockItemBankExerciseService();

        $answerRecord = $this->getItemBankAssessmentExerciseService()->startAnswer(1, 1, 1);

        $this->assertEquals($answerRecord['id'], 1);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionCode 5000305
     */
    public function testStartAnswer_whenModuleMiss_thenThrowCommonException()
    {
        $answerRecord = $this->getItemBankAssessmentExerciseService()->startAnswer(1, 1, 1);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionCode 5000305
     */
    public function testStartAnswer_whenAssessmentMiss_thenThrowCommonException()
    {
        $this->mockItemBankExerciseModuleService();
        $answerRecord = $this->getItemBankAssessmentExerciseService()->startAnswer(1, 1, 1);
    }

    /**
     * @expectedException \Biz\ItemBankExercise\ItemBankExerciseException
     * @expectedExceptionCode 4037501
     */
    public function testStartAnswer_whenCannotLearningExercise_thenThrowItemBankExerciseException()
    {
        $this->mockAssessmentExercise();
        $this->mockItemBankExerciseModuleService();

        $answerRecord = $this->getItemBankAssessmentExerciseService()->startAnswer(1, 1, 1);
    }

    /**
     * @expectedException \Biz\ItemBankExercise\ItemBankExerciseException
     * @expectedExceptionCode 5007513
     */
    public function testStartAnswer_whenAssessmentEnableFalse_thenThrowItemBankExerciseException()
    {
        $this->mockAssessmentExercise();
        $this->mockItemBankExerciseModuleService();
        $this->mockAnswerService();
        $this->mockBiz(
            'ItemBankExercise:ExerciseService',
            [
                [
                    'functionName' => 'canLearnExercise',
                    'returnValue' => ['code' => 'success'],
                ],
                [
                    'functionName' => 'get',
                    'returnValue' => ['assessmentEnable' => 0],
                ],
            ]
        );

        $answerRecord = $this->getItemBankAssessmentExerciseService()->startAnswer(1, 1, 1);
    }

    /**
     * @expectedException \Biz\ItemBankExercise\ItemBankExerciseException
     * @expectedExceptionCode 5007514
     */
    public function testStartAnswer_whenLatestRecordIsDoing_thenThrowItemBankExerciseException()
    {
        $this->mockAssessmentExercise();
        $this->mockItemBankExerciseModuleService();
        $this->mockAnswerService();
        $this->mockItemBankExerciseService();
        $this->mockBiz(
            'ItemBankExercise:AssessmentExerciseRecordService',
            [
                [
                    'functionName' => 'getLatestRecord',
                    'returnValue' => [
                        'status' => 'doing',
                    ],
                ],
            ]
        );

        $answerRecord = $this->getItemBankAssessmentExerciseService()->startAnswer(1, 1, 1);
    }

    public function testFindByExerciseIdAndModuleId()
    {
        $this->mockAssessmentExercise();
        $res = $this->getItemBankAssessmentExerciseDao()->findByExerciseIdAndModuleId(1, 1);

        $this->assertEquals(1, $res[0]['exerciseId']);
        $this->assertEquals(1, $res[0]['moduleId']);
        $this->assertEquals(1, $res[0]['assessmentId']);
    }

    public function testIsAssessmentExercise()
    {
        $this->mockAssessmentExercise();
        $res = $this->getItemBankAssessmentExerciseDao()->isAssessmentExercise(1, 1, 1);

        $this->assertEquals(1, $res['exerciseId']);
        $this->assertEquals(1, $res['moduleId']);
        $this->assertEquals(1, $res['assessmentId']);
    }

    public function testAddAssessments()
    {
        $this->getItemBankAssessmentExerciseService()->addAssessments(1, 1, [['id' => 1], ['id' => 2]]);
        $res = $this->getItemBankAssessmentExerciseService()->findByExerciseIdAndModuleId(1, 1);

        $this->assertEquals(2, count($res));
    }

    protected function mockAssessmentExercise()
    {
        $this->getItemBankAssessmentExerciseDao()->create([
            'exerciseId' => 1,
            'moduleId' => 1,
            'assessmentId' => 1,
        ]);
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
                        'type' => 'assessment',
                        'answerSceneId' => 1,
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
                    'returnValue' => ['assessmentEnable' => 1],
                ],
            ]
        );
    }

    protected function getItemBankAssessmentExerciseDao()
    {
        return $this->biz->dao('ItemBankExercise:AssessmentExerciseDao');
    }

    protected function getItemBankAssessmentExerciseService()
    {
        return $this->biz->service('ItemBankExercise:AssessmentExerciseService');
    }
}
