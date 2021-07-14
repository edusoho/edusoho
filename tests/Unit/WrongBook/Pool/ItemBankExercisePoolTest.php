<?php

namespace Tests\Unit\WrongBook\Pool;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\Dao\ExerciseDao;
use Biz\ItemBankExercise\Dao\ExerciseModuleDao;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;

class ItemBankExercisePoolTest extends BaseTestCase
{
    public function testPrepareSceneIds()
    {
        $pool = $this->createPool();
        $this->createBankExerciseAndModule();
        $exercisePool = $this->biz['wrong_question.exercise_pool'];
        $sceneIds = $exercisePool->prepareSceneIds($pool['id'], ['exerciseMediaType' => 'chapter']);
        $this->assertEquals([1], array_values($sceneIds));
    }

    public function testPrepareSceneIdsByTargetId()
    {
        $this->createBankExerciseAndModule();
        $exercisePool = $this->biz['wrong_question.exercise_pool'];
        $sceneIds = $exercisePool->prepareSceneIdsByTargetId(1, ['exerciseMediaType' => 'testpaper']);
        $this->assertEquals([2], array_values($sceneIds));
    }

    protected function createPool($poolFields = [])
    {
        $pool = array_merge([
            'user_id' => 1,
            'item_num' => 1,
            'target_type' => 'exercise',
            'target_id' => 1,
        ], $poolFields);

        return $this->getWrongQuestionBookPoolDao()->create($pool);
    }

    protected function createBankExerciseAndModule()
    {
        $bankExercise = [
            'title' => 'bank exercise',
            'questionBankId' => 1,
        ];
        $exercise = $this->getExerciseDao()->create($bankExercise);

        $exerciseModule1 = [
            'exerciseId' => $exercise['id'],
            'answerSceneId' => 1,
            'type' => 'chapter',
            'title' => '章节练习',
        ];
        $exerciseModule2 = [
            'exerciseId' => $exercise['id'],
            'answerSceneId' => 2,
            'type' => 'assessment',
            'title' => '模拟考试',
        ];
        $this->getItemBankExerciseModuleDao()->create($exerciseModule1);
        $this->getItemBankExerciseModuleDao()->create($exerciseModule2);
    }

    /**
     * @return WrongQuestionBookPoolDao
     */
    protected function getWrongQuestionBookPoolDao()
    {
        return $this->createDao('WrongBook:WrongQuestionBookPoolDao');
    }

    /**
     * @return ExerciseDao
     */
    protected function getExerciseDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseDao');
    }

    /**
     * @return ExerciseModuleDao
     */
    protected function getItemBankExerciseModuleDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseModuleDao');
    }
}
