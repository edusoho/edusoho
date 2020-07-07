<?php

namespace Tests\Unit\ItemBankExercise\ExpiryMode;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\ExpiryMode\ExpiryModeFactory;
use Biz\ItemBankExercise\Service\ExerciseService;

class DaysExpiryModeTest extends BaseTestCase
{
    public function testGetDeadline()
    {
        $exercise = $this->createExercise();
        $except = $exercise['expiryDays'] * 24 * 60 * 60 + strtotime(date('Y-m-d', time()).' 23:59:59');
        $res = ExpiryModeFactory::create($exercise['expiryMode'])->getDeadline($exercise);

        $this->assertEquals($except, $res);
    }

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
            $exercise,
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
                'expiryMode' => 'days',
                'expiryDays' => 1,
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
