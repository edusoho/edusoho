<?php

namespace Tests\Unit\ItemBankExercise\ExpiryMode;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\Dao\ExerciseMemberDao;
use Biz\ItemBankExercise\ExpiryMode\ExpiryModeFactory;
use Biz\ItemBankExercise\Service\ExerciseService;

class ExpiryModeTest extends BaseTestCase
{
    public function testFilterUpdateExpiryInfo()
    {
        $exercise = $this->createExercise();
        $fields = [
            'expiryDays' => 0,
            'expiryStartDate' => 0,
            'expiryEndDate' => 0,
        ];
        $res = ExpiryModeFactory::create($exercise['expiryMode'])->filterUpdateExpiryInfo($exercise, $fields);

        $this->assertEquals($fields['expiryDays'], $res['expiryDays']);
        $this->assertEquals($fields['expiryStartDate'], $res['expiryStartDate']);
        $this->assertEquals($fields['expiryEndDate'], $res['expiryEndDate']);
    }

    public function testGetDeadlineByWaveType()
    {
        $exercise = $this->createExercise();
        $member = $this->getExerciseMemberDao()->create(
            [
                'exerciseId' => $exercise['id'],
                'questionBankId' => 1,
                'userId' => 1,
                'role' => 'student',
                'remark' => 'aaa',
                'deadline' => 0,
            ]
        );
        $except = time() + 1 * 24 * 60 * 60;
        $res = ExpiryModeFactory::create($exercise['expiryMode'])->getDeadlineByWaveType($member['deadline'], 'plus', 1);

        $this->assertEquals($except, $res);
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
