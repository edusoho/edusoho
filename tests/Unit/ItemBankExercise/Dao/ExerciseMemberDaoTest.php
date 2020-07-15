<?php

namespace Tests\Unit\ItemBankExercise\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ExerciseMemberDaoTest extends BaseDaoTestCase
{
    public function testGetByExerciseIdAndUserId()
    {
        $this->createMember();
        $res = $this->getDao()->getByExerciseIdAndUserId(1, 1);
        $this->assertEquals(1, $res['exerciseId']);
        $this->assertEquals(1, $res['userId']);
    }

    public function testFindByUserIdAndRole()
    {
        $this->batchCreateMembers();
        $res = $this->getDao()->findByUserIdAndRole(1, 'teacher');
        $this->assertEquals(1, count($res));
        $this->assertEquals('teacher', $res[0]['role']);
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

    private function batchCreateMembers()
    {
        return $this->getDao()->batchCreate(
            [
                [
                    'exerciseId' => 1,
                    'questionBankId' => 1,
                    'userId' => 1,
                    'role' => 'student',
                    'remark' => 'adg',
                ],
                [
                    'exerciseId' => 1,
                    'questionBankId' => 1,
                    'userId' => 2,
                    'role' => 'teacher',
                    'remark' => 'adg',
                ],
                [
                    'exerciseId' => 2,
                    'questionBankId' => 2,
                    'userId' => 1,
                    'role' => 'teacher',
                    'remark' => 'adg',
                ]
            ]
        );
    }
}
