<?php

namespace Tests\Unit\ItemBankExercise\ExpiryMode;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\ExpiryMode\ExpiryModeFactory;
use Biz\ItemBankExercise\Service\ExerciseService;

class ForeverExpiryModeTest extends BaseTestCase
{
    public function testValidateExpiryMode()
    {
        $exercise = $this->createExercise();
        $res = ExpiryModeFactory::create($exercise['expiryMode'])->validateExpiryMode($exercise);

        $this->assertEquals($exercise['expiryMode'], $res['expiryMode']);
        $this->assertEquals($exercise['expiryDays'], $res['expiryDays']);
        $this->assertEquals($exercise['expiryStartDate'], $res['expiryStartDate']);
        $this->assertEquals($exercise['expiryEndDate'], $res['expiryEndDate']);
    }

    public function testGetUpdateDeadline()
    {
        $exercise = $this->createExercise();
        $res = ExpiryModeFactory::create($exercise['expiryMode'])->getUpdateDeadline(
            ['deadline' => 0],
            ['deadline' => time()]
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
                'expiryMode' => 'forever',
            ]
        );
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }
}
