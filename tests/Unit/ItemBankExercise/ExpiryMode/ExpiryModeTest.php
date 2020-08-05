<?php

namespace Tests\Unit\ItemBankExercise\ExpiryMode;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\Dao\ExerciseMemberDao;
use Biz\ItemBankExercise\ExpiryMode\ExpiryModeFactory;
use Biz\ItemBankExercise\Service\ExerciseService;

class ExpiryModeTest extends BaseTestCase
{
    public function testGetUpdateDeadline()
    {
        $exercise = $this->createExercise();
        $res = ExpiryModeFactory::create($exercise['expiryMode'])->getUpdateDeadline(
            ['deadline' => 0],
            ['deadline' => time(), 'updateType' => 'deadline']
        );

        $this->assertEquals(time(), $res);
    }

    private function createExercise()
    {
        return $this->getExerciseService()->create(
            [
                'id' => 1,
                'title' => 'test',
                'questionBankId' => 1,
                'categoryId' => 1,
                'seq' => 1,
                'expiryMode' => 'date',
                'expiryStartDate' => strtotime('-1day'),
                'expiryEndDate' => strtotime('+1day'),
            ]
        );
    }

    /**
     * @return ExerciseMemberDao
     */
    protected function getExerciseMemberDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseMemberDao');
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }
}
