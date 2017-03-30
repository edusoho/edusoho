<?php

namespace Tests\Unit\Classroom\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ClassroomMemberDaoTest extends BaseDaoTestCase
{
    public function testCountMobileFilledMembersByClassroomId()
    {
        $this->mockDataObject();
        $res = $this->getDao()->countMobileFilledMembersByClassroomId(1);
        $this->assertEquals(0, $res);

        $this->getUserSerivice()->register(array(
            'nickname' => 'test',
            'email' => 'test@admin.com',
            'password' => 'test',
            'verifiedMobile' => '13967340627',
            'mobile' => '13967340627',
        ));
        $this->mockDataObject(array('userId' => 2));
        $res = $this->getDao()->countMobileFilledMembersByClassroomId(1);
        $this->assertEquals(1, $res);

        $this->getUserSerivice()->register(array(
            'nickname' => 'test2',
            'email' => 'test2@admin.com',
            'password' => 'test2',
            'verifiedMobile' => '13967340600',
            'mobile' => '13967340600',
        ));
        $this->getUserSerivice()->lockUser(3);
        $this->mockDataObject(array('userId' => 3));
        $res = $this->getDao()->countMobileFilledMembersByClassroomId(1, 1);
        $this->assertEquals(1, $res);

        $this->getUserSerivice()->register(array(
            'nickname' => 'test3',
            'email' => 'test3@admin.com',
            'password' => 'test3',
            'verifiedMobile' => '13967340627',
            'mobile' => '13967340627',
        ));
        $this->mockDataObject(array('userId' => 4));
        $res = $this->getDao()->countMobileFilledMembersByClassroomId(1);
        $this->assertEquals(2, $res);
    }

    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('classroomId' => 2));
        $expected[] = $this->mockDataObject(array('noteNum' => 2));
        $expected[] = $this->mockDataObject(array('role' => array('teacher')));
        $expected[] = $this->mockDataObject(array('createdTime' => 2));
        $testCondition = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 5,
                ),
            array(
                'condition' => array('userId' => 2),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('classroomId' => 2),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('noteNumGreaterThan' => 1),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('role' => 'teacher'),
                'expectedResults' => array($expected[3]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('startTimeGreaterThan' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 5,
                ),
            array(
                'condition' => array('createdTime_GE' => 2),
                'expectedResults' => $expected,
                'expectedCount' => 5,
                ),
            array(
                'condition' => array('startTimeLessThan' => 3),
                'expectedResults' => array($expected[4]),
                'expectedCount' => 0,
                ),
            );
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testCountStudents()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('role' => array('student', 'teacher')));
        $expected[] = $this->mockDataObject(array('role' => array('student')));
        $res = $this->getDao()->countStudents(1);
        $this->assertEquals(2, $res);
    }

    public function testCountAuditors()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('role' => array('auditor', 'teacher')));
        $expected[] = $this->mockDataObject(array('role' => array('auditor')));
        $res = $this->getDao()->countAuditors(1);
        $this->assertEquals(2, $res);
    }

    public function testFindAssistantsByClassroomId()
    {
    }

    public function testFindTeachersByClassroomId()
    {
    }

    public function testFindByUserIdAndClassroomIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('classroomId' => 1));
        $expected[] = $this->mockDataObject(array('classroomId' => 2));
        $res = $this->getDao()->findByUserIdAndClassroomIds(1, array(1, 2));
        $this->assertArrayEquals($expected, $res);
    }

    public function testGetByClassroomIdAndUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->getByClassroomIdAndUserId(1, 1);
        $this->assertArrayEquals($expected[0], $res);
    }

    public function testFindByClassroomIdAndUserIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('userId' => 1));
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $res = $this->getDao()->findByClassroomIdAndUserIds(1, array(1, 2));
        $this->assertArrayEquals($expected, $res);
    }

    public function testDeleteByClassroomIdAndUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->deleteByClassroomIdAndUserId(1, 1);
        $this->assertEquals('1', $res);
    }

    public function testCountMobileVerifiedMembersByClassroomId()
    {
    }

    public function testFindByClassroomIdAndRole()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('userId' => 1));
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $res = $this->getDao()->findByClassroomIdAndRole(1, 'student', 0, PHP_INT_MAX);
        $this->assertArrayEquals($expected[0], $res[0]);
        $this->assertArrayEquals($expected[1], $res[1]);
    }

    public function testFindMemberIdsByClassroomId()
    {
    }

    public function testFindByUserId()
    {
    }

    protected function getDefaultMockFields()
    {
        return array(
            'userId' => 1,
            'classroomId' => 1,
            'noteNum' => 1,
            'role' => array('student'),
            'createdTime' => 0,
            );
    }

    private function getUserSerivice()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }
}
