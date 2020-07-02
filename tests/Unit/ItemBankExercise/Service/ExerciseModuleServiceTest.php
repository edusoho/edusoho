<?php

namespace Tests\Unit\ItemBankExercise\Service;

use Biz\BaseTestCase;

class ExerciseModuleServiceTest extends BaseTestCase
{
    public function testGet()
    {
        $this->mockExerciseModule();

        $module = $this->getItemBankExerciseModuleService()->get(1);

        $this->assertEquals($module['id'], 1);
    }

    public function testSearch()
    {
        $this->mockExerciseModule();

        $modules = $this->getItemBankExerciseModuleService()->search(['exerciseId' => 1], [], 0, 2);

        $this->assertEquals(count($modules), 2);
    }

    public function testCount()
    {
        $this->mockExerciseModule();

        $count = $this->getItemBankExerciseModuleService()->count(['exerciseId' => 1]);

        $this->assertEquals($count, 2);
    }

    public function testFindByExerciseId()
    {
        $this->mockExerciseModule();

        $modules = $this->getItemBankExerciseModuleService()->findByExerciseId(1);

        $this->assertEquals(count($modules), 2);
    }

    public function testFindByExerciseIdAndType()
    {
        $this->mockExerciseModule();

        $modules = $this->getItemBankExerciseModuleService()->findByExerciseIdAndType(1, 'assessment');

        $this->assertEquals(count($modules), 1);
    }

    public function testCreateAssessmentModule()
    {
        $this->mockItemBankExerciseService();

        $module = $this->getItemBankExerciseModuleService()->createAssessmentModule(1, '试卷练习');

        $this->assertEquals($module['exerciseId'], 1);
        $this->assertEquals($module['title'], '试卷练习');
    }

    /**
     * @expectedException \Biz\ItemBankExercise\ItemBankExerciseException
     * @expectedExceptionCode 5007515
     */
    public function testCreateAssessmentModule_whenModuleCountGt5_thenThrowItemBankExerciseException()
    {
        $this->mockItemBankExerciseService();

        for ($i = 1; $i <= 5; ++$i) {
            $this->getItemBankExerciseModuleDao()->create([
                'seq' => 1,
                'exerciseId' => 1,
                'answerSceneId' => 1,
                'title' => '模拟考试',
                'type' => 'assessment',
            ]);
        }

        $this->getItemBankExerciseModuleService()->createAssessmentModule(1, '试卷练习');
    }

    protected function mockItemBankExerciseService()
    {
        $this->mockBiz(
            'ItemBankExercise:ExerciseService',
            [
                [
                    'functionName' => 'tryManageExercise',
                    'returnValue' => true,
                ],
            ]
        );
    }

    protected function mockExerciseModule()
    {
        $this->getItemBankExerciseModuleDao()->create([
            'id' => 1,
            'seq' => 1,
            'exerciseId' => 1,
            'answerSceneId' => 1,
            'title' => '模拟考试',
            'type' => 'assessment',
        ]);

        $this->getItemBankExerciseModuleDao()->create([
            'id' => 2,
            'seq' => 1,
            'exerciseId' => 1,
            'answerSceneId' => 2,
            'title' => '章节练习',
            'type' => 'chapter',
        ]);
    }

    protected function getItemBankExerciseModuleService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseModuleService');
    }

    protected function getItemBankExerciseModuleDao()
    {
        return $this->biz->dao('ItemBankExercise:ExerciseModuleDao');
    }
}
