<?php


namespace Tests\Unit\ItemBankExercise\Dao;


use Tests\Unit\Base\BaseDaoTestCase;

class ExerciseMemberDaoTest extends BaseDaoTestCase
{
    public function testGetByExerciseIdAndUserId()
    {
       $this->createMember();
       $res = $this->getDao()->getByExerciseIdAndUserId(1,1);
       $this->assertEquals(1, $res['exerciseId']);
       $this->assertEquals(1, $res['userId']);
    }

    protected function getDefaultMockFields()
    {
        return [
            'exerciseId' => 1,
            'userId' => 1,
            'remark' => 'adg',
            'questionBankId' => 1,
        ];
    }

    private function createMember()
    {
        return $this->getDao()->create(
            [
                'exerciseId' => 1,
                'questionBankId' => 1,
                'userId' => 1,
                'remark' => 'adg',
            ]
        );
    }
}