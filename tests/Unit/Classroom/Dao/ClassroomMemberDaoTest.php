<?php

namespace Tests\Unit\Classroom\Dao;

use Biz\Sign\Dao\SignUserStatisticsDao;
use Tests\Unit\Base\BaseDaoTestCase;

class ClassroomMemberDaoTest extends BaseDaoTestCase
{
    public function testSearchMembersByClassroomId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['classroomId' => 1, 'role' => ['teacher'], 'deadline' => 0]);
        $expected[] = $this->mockDataObject(['classroomId' => 1, 'role' => ['student'], 'deadline' => 0]);
        $expected[] = $this->mockDataObject(['classroomId' => 1, 'role' => ['student'], 'deadline' => strtotime('+5day')]);
        $expected[] = $this->mockDataObject(['classroomId' => 1, 'role' => ['student'], 'deadline' => strtotime('-2day')]);

        $result1 = $this->getDao()->searchMembersByClassroomId(1, ['role' => '%|teacher|%'], 0, 5);
        $result2 = $this->getDao()->searchMembersByClassroomId(1, ['role' => '%|student|%', 'in_validity' => ['deadline_GT' => time(), 'deadline_EQ' => 0]], 0, 5);
        $this->assertArrayEquals($expected[0], $result1[0]);
        $this->assertArrayEquals($expected[1], $result2[0]);
        $this->assertArrayEquals($expected[2], $result2[1]);
    }

    public function testCountMembersByClassroomId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['classroomId' => 1, 'role' => ['teacher'], 'deadline' => 0]);
        $expected[] = $this->mockDataObject(['classroomId' => 1, 'role' => ['student'], 'deadline' => 0]);
        $expected[] = $this->mockDataObject(['classroomId' => 1, 'role' => ['student'], 'deadline' => strtotime('+5day')]);
        $expected[] = $this->mockDataObject(['classroomId' => 1, 'role' => ['student'], 'deadline' => strtotime('-2day')]);

        $result1 = $this->getDao()->countMembersByClassroomId(1, ['role' => '%|teacher|%']);
        $result2 = $this->getDao()->countMembersByClassroomId(1, ['role' => '%|student|%', 'in_validity' => ['deadline_GT' => time(), 'deadline_EQ' => 0]]);
        $this->assertEquals(1, $result1);
        $this->assertEquals(2, $result2);
    }

    public function testCountMobileFilledMembersByClassroomId()
    {
        $member = $this->mockDataObject();
        $res1 = $this->getDao()->countMobileFilledMembersByClassroomId($member['classroomId']);
        $this->assertEquals(0, $res1);

        $user = $this->getUserSerivice()->register([
            'nickname' => 'test',
            'email' => 'test@admin.com',
            'password' => 'test123',
            'verifiedMobile' => '13967340627',
            'mobile' => '13967340627',
        ]);
        $member = $this->mockDataObject(['userId' => $user['id']]);
        $res = $this->getDao()->countMobileFilledMembersByClassroomId($member['classroomId']);
        $this->assertEquals(1, $res);

        $user = $this->getUserSerivice()->register([
            'nickname' => 'test2',
            'email' => 'test2@admin.com',
            'password' => 'test1234',
            'verifiedMobile' => '13967340600',
            'mobile' => '13967340600',
        ]);
        $this->getUserSerivice()->lockUser($user['id']);
        $member = $this->mockDataObject(['userId' => $user['id']]);
        $res = $this->getDao()->countMobileFilledMembersByClassroomId($member['classroomId'], 1);
        $this->assertEquals(1, $res);

        $user = $this->getUserSerivice()->register([
            'nickname' => 'test3',
            'email' => 'test3@admin.com',
            'password' => 'test123456',
            'verifiedMobile' => '13967340627',
            'mobile' => '13967340627',
        ]);
        $member = $this->mockDataObject(['userId' => $user['id']]);
        $res = $this->getDao()->countMobileFilledMembersByClassroomId($member['classroomId']);
        $this->assertEquals(2, $res);
    }

    public function testSearch()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['userId' => 2]);
        $expected[] = $this->mockDataObject(['classroomId' => 2]);
        $expected[] = $this->mockDataObject(['noteNum' => 2]);
        $expected[] = $this->mockDataObject(['role' => ['teacher']]);
        $expected[] = $this->mockDataObject(['createdTime' => 2]);
        $testCondition = [
            [
                'condition' => [],
                'expectedResults' => $expected,
                'expectedCount' => 5,
            ],
            [
                'condition' => ['userId' => 2],
                'expectedResults' => [$expected[0]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['classroomId' => 2],
                'expectedResults' => [$expected[1]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['noteNumGreaterThan' => 1],
                'expectedResults' => [$expected[2]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['role' => 'teacher'],
                'expectedResults' => [$expected[3]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['startTimeGreaterThan' => 1],
                'expectedResults' => $expected,
                'expectedCount' => 5,
            ],
            [
                'condition' => ['createdTime_GE' => 2],
                'expectedResults' => $expected,
                'expectedCount' => 5,
            ],
            [
                'condition' => ['startTimeLessThan' => 3],
                'expectedResults' => [$expected[4]],
                'expectedCount' => 0,
            ],
        ];
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testUpdateByClassroomIdAndRole()
    {
        $expected = $this->mockDataObject(['role' => ['student']]);
        $result = $this->getDao()->updateByClassroomIdAndRole(
            1,
            'student',
            ['role' => ['teacher']]
        );

        $this->assertEquals(0, $result);
    }

    public function testFindMembersByUserIdAndClassroomIds()
    {
        $result1 = $this->getDao()->findMembersByUserIdAndClassroomIds(1, []);

        $this->assertEquals([], $result1);

        $expected = [];
        $expected[] = $this->mockDataObject(['classroomId' => 1]);
        $expected[] = $this->mockDataObject(['classroomId' => 2]);
        $result2 = $this->getDao()->findMembersByUserIdAndClassroomIds(1, [1, 2]);

        $this->assertArrayEquals($expected[0], $result2[0]);
        $this->assertArrayEquals($expected[1], $result2[1]);
    }

    public function testFindMembersByUserId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['classroomId' => 1]);
        $expected[] = $this->mockDataObject(['classroomId' => 2]);
        $result = $this->getDao()->findMembersByUserId(1);

        $this->assertArrayEquals($expected[0], $result[0]);
        $this->assertArrayEquals($expected[1], $result[1]);
    }

    public function testCountStudents()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['role' => ['student', 'teacher']]);
        $expected[] = $this->mockDataObject(['role' => ['student']]);
        $res = $this->getDao()->countStudents(1);
        $this->assertEquals(2, $res);
    }

    public function testCountAuditors()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['role' => ['auditor', 'teacher']]);
        $expected[] = $this->mockDataObject(['role' => ['auditor']]);
        $res = $this->getDao()->countAuditors(1);
        $this->assertEquals(2, $res);
    }

    public function testFindAssistantsByClassroomId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['role' => ['assistant', 'teacher']]);
        $expected[] = $this->mockDataObject(['role' => ['assistant']]);
        $result = $this->getDao()->findAssistantsByClassroomId(1);

        $this->assertArrayEquals($expected[0], $result[0]);
        $this->assertArrayEquals($expected[1], $result[1]);
    }

    public function testFindTeachersByClassroomId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['role' => ['auditor', 'teacher']]);
        $expected[] = $this->mockDataObject(['role' => ['teacher']]);
        $result = $this->getDao()->findTeachersByClassroomId(1);

        $this->assertArrayEquals($expected[1], $result[1]);
    }

    public function testFindByUserIdAndClassroomIds()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['classroomId' => 1]);
        $expected[] = $this->mockDataObject(['classroomId' => 2]);
        $res = $this->getDao()->findByUserIdAndClassroomIds(1, [1, 2]);
        $this->assertArrayEquals($expected, $res);
    }

    public function testGetByClassroomIdAndUserId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->getByClassroomIdAndUserId(1, 1);
        $this->assertArrayEquals($expected[0], $res);
    }

    public function testFindByClassroomIdAndUserIds()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['userId' => 1]);
        $expected[] = $this->mockDataObject(['userId' => 2]);
        $res = $this->getDao()->findByClassroomIdAndUserIds(1, [1, 2]);
        $this->assertArrayEquals($expected, $res);
    }

    public function testDeleteByClassroomIdAndUserId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->deleteByClassroomIdAndUserId(1, 1);
        $this->assertEquals('1', $res);
    }

    public function testFindByClassroomIdAndRole()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['userId' => 1]);
        $expected[] = $this->mockDataObject(['userId' => 2]);
        $res = $this->getDao()->findByClassroomIdAndRole(1, 'student', 0, PHP_INT_MAX);
        $this->assertArrayEquals($expected[0], $res[0]);
        $this->assertArrayEquals($expected[1], $res[1]);
    }

    public function testFindByUserId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['classroomId' => 1]);
        $expected[] = $this->mockDataObject(['classroomId' => 2]);
        $result = $this->getDao()->findByUserId(1);

        $this->assertArrayEquals($expected[0], $result[0]);
        $this->assertArrayEquals($expected[1], $result[1]);
    }

    public function testSearchMemberCountGroupByFields()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['classroomId' => 1]);
        $expected[] = $this->mockDataObject(['classroomId' => 2]);

        $conditions = ['createdTime_GE' => strtotime('-30 days'), 'roles' => ['student', 'assistant']];
        $result = $this->getDao()->searchMemberCountGroupByFields($conditions, 'classroomId', 0, 10);

        $this->assertEquals(2, count($result));
    }

    public function testSearchSignStatisticsByClassroomId()
    {
        $member1 = $this->mockDataObject(['classroomId' => 1, 'userId' => 1]);
        $member2 = $this->mockDataObject(['classroomId' => 1, 'userId' => 2]);
        $member3 = $this->mockDataObject(['classroomId' => 1, 'userId' => 3]);
        $signUserStatistics1 = $this->createSignStatistics(['userId' => 1, 'signDays' => '3', 'keepDays' => 1]);
        $signUserStatistics2 = $this->createSignStatistics(['userId' => 2, 'signDays' => '4', 'keepDays' => 4]);
        $signUserStatistics3 = $this->createSignStatistics(['userId' => 3, 'signDays' => '2', 'keepDays' => 2]);
        $result1 = $this->getDao()->searchSignStatisticsByClassroomId(1, ['userIds' => [$member1['userId'], $member2['userId'], $member3['userId']]], ['keepDays' => 'DESC'], 0, 10);
        $result2 = $this->getDao()->searchSignStatisticsByClassroomId(1, ['userIds' => [$member1['userId'], $member2['userId'], $member3['userId']]], ['signDays' => 'DESC'], 0, 10);
        $this->assertCount(3, $result1);
        $this->assertCount(3, $result2);

        $this->assertEquals(
            array_merge($member2, [
                'signDays' => $signUserStatistics2['signDays'],
                'keepDays' => $signUserStatistics2['keepDays'],
                'lastSignTime' => $signUserStatistics2['lastSignTime'],
            ]),
            $result1[0]
        );
        $this->assertEquals(
            array_merge($member3, [
                'signDays' => $signUserStatistics3['signDays'],
                'keepDays' => $signUserStatistics3['keepDays'],
                'lastSignTime' => $signUserStatistics3['lastSignTime'],
            ]),
            $result1[1]
        );
        $this->assertEquals(
            array_merge($member2, [
                'signDays' => $signUserStatistics2['signDays'],
                'keepDays' => $signUserStatistics2['keepDays'],
                'lastSignTime' => $signUserStatistics2['lastSignTime'],
            ]),
            $result2[0]
        );
        $this->assertEquals(
            array_merge($member1, [
                'signDays' => $signUserStatistics1['signDays'],
                'keepDays' => $signUserStatistics1['keepDays'],
                'lastSignTime' => $signUserStatistics1['lastSignTime'],
            ]),
            $result2[1]
        );
    }

    protected function getDefaultMockFields()
    {
        return [
            'userId' => 1,
            'classroomId' => 1,
            'noteNum' => 1,
            'role' => ['student'],
            'createdTime' => 0,
        ];
    }

    private function createSignStatistics(array $sign = [])
    {
        return $this->getSignUserStatisticsDao()->create(array_merge([
            'userId' => 1,
            'targetType' => 'classroom_sign',
            'targetId' => '1',
            'keepDays' => 3,
            'signDays' => 5,
            'lastSignTime' => time(),
        ], $sign));
    }

    private function getUserSerivice()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }

    /**
     * @return SignUserStatisticsDao
     */
    private function getSignUserStatisticsDao()
    {
        return $this->getBiz()->dao('Sign:SignUserStatisticsDao');
    }
}
