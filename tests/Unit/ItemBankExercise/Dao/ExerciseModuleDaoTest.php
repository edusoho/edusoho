<?php

namespace Tests\Unit\ItemBankExercise\Dao;

use Biz\BaseTestCase;

class ExerciseModuleDaoTest extends BaseTestCase
{
    public function testFindByExerciseId()
    {
        $this->mockExerciseModule();

        $modules = $this->getItemBankExerciseModuleDao()->findByExerciseId(1);

        $this->assertEquals(count($modules), 2);
    }

    public function testFindByExerciseIdAndType()
    {
        $this->mockExerciseModule();

        $modules = $this->getItemBankExerciseModuleDao()->findByExerciseIdAndType(1, 'assessment');

        $this->assertEquals(count($modules), 1);
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

    protected function getItemBankExerciseModuleDao()
    {
        return $this->biz->dao('ItemBankExercise:ExerciseModuleDao');
    }
}
