<?php

namespace Tests\Unit\ItemBankExercise\ExpiryMode;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\ExpiryMode\ExpiryModeFactory;
use Biz\ItemBankExercise\Service\ExerciseService;

class EndDateExpiryModeTest extends BaseTestCase
{
    public function testGetDeadline()
    {
        $exercise = $this->createExercise();
        $except = strtotime('+1day');
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

    public function testIsExpired()
    {
        $exercise = $this->createExercise();
        $res = ExpiryModeFactory::create($exercise['expiryMode'])->isExpired($exercise);

        $this->assertFalse($res);
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
                'expiryMode' => 'end_date',
                'expiryEndDate' => strtotime('+1day'),
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
