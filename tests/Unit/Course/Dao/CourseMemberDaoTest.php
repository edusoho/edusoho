<?php

namespace Tests\Unit\Course\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class CourseMemberDaoTest extends BaseDaoTestCase
{
    public function testFindByCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 3));

        $values = array();
        foreach ($expected as $val) {
            $values[] = $val;
        }

        $res = array();
        $res[] = $this->getDao()->findByCourseId(1);
        $res[] = $this->getDao()->findByCourseId(2);

        $this->assertEquals($values, $res[0]);
        $this->assertEquals(array(), $res[1]);
    }

    public function testFindByUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 3));

        $values = array();
        foreach ($expected as $val) {
            $values[] = $val;
        }

        $res = array();
        $res[] = $this->getDao()->findByUserId(1);
        $res[] = $this->getDao()->findByUserId(2);

        $this->assertEquals($values, $res[0]);
        $this->assertEquals(array(), $res[1]);
    }

    public function testFindByCourseIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 2, 'courseId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByCourseIds(array(1, 3));
        $res[] = $this->getDao()->findByCourseIds(array(1, 2));
        $res[] = $this->getDao()->findByCourseIds(array(3));

        $this->assertEquals(array($expected[0]), $res[0]);
        $this->assertEquals($expected, $res[1]);
        $this->assertEquals(array(), $res[2]);
    }

    public function testGetByCourseIdAndUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->getByCourseIdAndUserId(1, 1);
        $res[] = $this->getDao()->getByCourseIdAndUserId(2, 1);
        $res[] = $this->getDao()->getByCourseIdAndUserId(1, 2);

        foreach ($expected as $key => $val) {
            $this->assertEquals($val, $res[$key]);
        }
    }

    public function testFindLearnedByCourseIdAndUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->findLearnedByCourseIdAndUserId(1, 1);
        $res[] = $this->getDao()->findLearnedByCourseIdAndUserId(2, 1);
        $res[] = $this->getDao()->findLearnedByCourseIdAndUserId(1, 2);

        foreach ($expected as $key => $val) {
            $this->assertEquals(array($val), $res[$key]);
        }
    }

    public function testFindByCourseIdAndRole()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 2, 'role' => 'teacher'));

        $res = array();
        $res[] = $this->getDao()->findByCourseIdAndRole(1, 'student');
        $res[] = $this->getDao()->findByCourseIdAndRole(2, 'student');
        $res[] = $this->getDao()->findByCourseIdAndRole(1, 'teacher');

        foreach ($expected as $key => $val) {
            $this->assertEquals(array($val), $res[$key]);
        }
    }

    public function testFindByUserIdAndJoinType()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('courseId' => 2, 'joinedType' => 'classroom'));
        $expected[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByUserIdAndJoinType(1, 'course');
        $res[] = $this->getDao()->findByUserIdAndJoinType(1, 'classroom');
        $res[] = $this->getDao()->findByUserIdAndJoinType(2, 'course');

        foreach ($expected as $key => $val) {
            $this->assertEquals(array($val), $res[$key]);
        }
    }

    public function testDeleteByCourseIdAndRole()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 2, 'role' => 'teacher'));

        $this->getDao()->deleteByCourseIdAndRole(1, 'student');
        $this->getDao()->deleteByCourseIdAndRole(2, 'teacher');

        $res = array();
        $res[] = $this->getDao()->findByCourseIdAndRole(1, 'student');
        $res[] = $this->getDao()->findByCourseIdAndRole(2, 'student');
        $res[] = $this->getDao()->findByCourseIdAndRole(1, 'teacher');

        $this->assertEquals(array(), $res[0]);
        $this->assertEquals(array($expected[1]), $res[1]);
        $this->assertEquals(array($expected[2]), $res[2]);
    }

    public function testDeleteByCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 2));

        $this->getDao()->deleteByCourseId(1);
        $this->getDao()->deleteByCourseId(3);

        $res = array();
        $res[] = $this->getDao()->findByCourseIds(array(1));
        $res[] = $this->getDao()->findByCourseIds(array(2));

        $this->assertEquals(array(), $res[0]);
        $this->assertEquals(array($expected[1]), $res[1]);
    }

    public function testFindByUserIdAndCourseIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByUserIdAndCourseIds(1, array(1));
        $res[] = $this->getDao()->findByUserIdAndCourseIds(2, array(1, 2));
        $res[] = $this->getDao()->findByUserIdAndCourseIds(1, array(1, 2));

        $this->assertEquals(array($expected[0]), $res[0]);
        $this->assertEquals(array($expected[2]), $res[1]);
        $this->assertEquals(array($expected[0], $expected[1]), $res[2]);
    }

    public function testSearchMember()
    {
        $this->mockCourse();

        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 2, 'role' => 'teacher'));

        $res1 = $this->getDao()->search(array(), array(), 0, 10);
        $res2 = $this->getDao()->search(array('userId' => 1), array(), 0, 10);

        $this->assertEquals(array($expected[0], $expected[1], $expected[2]), $res1);
        $this->assertEquals(array($expected[0], $expected[2]), $res2);
    }

    public function testCountMember()
    {
        $this->mockCourse();

        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 2, 'role' => 'teacher'));

        $res1 = $this->getDao()->count(array(), array(), 0, 10);
        $res2 = $this->getDao()->count(array('userId' => 1), array(), 0, 10);

        $this->assertEquals(3, $res1);
        $this->assertEquals(2, $res2);
    }

    public function testSearchMemberCountGroupByFields()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 2, 'role' => 'teacher'));

        $res = array();
        $res[] = $this->getDao()->searchMemberCountGroupByFields(array('courseId' => 1), 'courseId', 0, 10);
        $res[] = $this->getDao()->searchMemberCountGroupByFields(array('courseId' => 1, 'userId' => 1), 'courseId', 0,
            10);

        $this->assertEquals(array(array('courseId' => '1', 'count' => '2')), $res[0]);
        $this->assertEquals(array(array('courseId' => '1', 'count' => '1')), $res[1]);
    }

    public function testFindByUserIdAndRole()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 2, 'role' => 'teacher'));

        $res = array();
        $res[] = $this->getDao()->findByUserIdAndRole(1, 'student');
        $res[] = $this->getDao()->findByUserIdAndRole(2, 'student');
        $res[] = $this->getDao()->findByUserIdAndRole(1, 'teacher');

        foreach ($expected as $key => $val) {
            $this->assertEquals(array($val), $res[$key]);
        }
    }

    public function testFindByUserIdAndCourseSetIdAndRole()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2, 'courseSetId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 2, 'role' => 'teacher'));

        $res = array();
        $res[] = $this->getDao()->findByUserIdAndCourseSetIdAndRole(1, 1, 'student');
        $res[] = $this->getDao()->findByUserIdAndCourseSetIdAndRole(2, 2, 'student');
        $res[] = $this->getDao()->findByUserIdAndCourseSetIdAndRole(1, 1, 'teacher');

        foreach ($expected as $key => $val) {
            $this->assertEquals(array($val), $res[$key]);
        }
    }

    public function testFindMembersNotInClassroomByUserIdAndRole()
    {
        $this->mockCourse();

        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 2, 'role' => 'teacher'));

        $res = array();
        $res[] = $this->getDao()->findMembersNotInClassroomByUserIdAndRole(1, 'student', 0, 10, false);
        $res[] = $this->getDao()->findMembersNotInClassroomByUserIdAndRole(2, 'student', 0, 10, false);
        $res[] = $this->getDao()->findMembersNotInClassroomByUserIdAndRole(2, 'teacher', 0, 10, false);

        $this->assertEquals(array($expected[0]), $res[0]);
        $this->assertEquals(array($expected[1]), $res[1]);
        $this->assertEquals(array(), $res[2]);
    }

    /**
     * @group current
     */
    public function testSearchMemberIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2, 'courseSetId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 2, 'role' => 'teacher'));

        $res = $this->getDao()->searchMemberIds(array('unique' => true), array('createdTime' => 'ASC'), 0, 10);

        $this->assertEquals(array(array('userId' => $expected[0]['userId']), array('userId' => $expected[1]['userId'])),
            $res);
    }

    public function testUpdateMembers()
    {
        $tmp = $this->getDefaultMockFields();
        $tmp['userId'] = '2';
        $tmp['courseId'] = '2';
        $tmp['role'] = 'teacher';

        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $tmp;
        $expected[] = $this->mockDataObject(array('courseId' => 2));

        $this->getDao()->updateMembers(
            array('userId' => 1, 'courseId' => 2),
            array('userId' => 2, 'role' => 'teacher')
        );

        $res = array();
        $res[] = $this->getDao()->getByCourseIdAndUserId(1, 1);
        $res[] = $this->getDao()->getByCourseIdAndUserId(1, 2);
        $users = $this->getDao()->findByUserIdAndRole(2, 'teacher');
        $tmp = $users[0];
        unset($tmp['id']);
        unset($tmp['createdTime']);
        unset($tmp['updatedTime']);

        $res[] = $tmp;

        foreach ($res as $key => $val) {
            $this->assertEquals($expected[$key], $val);
        }
    }

    // Todo 跨表
    public function testCountThreadsByCourseIdAndUserId()
    {
    }

    // Todo 跨表
    public function testCountActivitiesByCourseIdAndUserId()
    {
    }

    // Todo 跨表
    public function testCountPostsByCourseIdAndUserId()
    {
    }

    protected function getDefaultMockFields()
    {
        return array(
            'courseId' => '1',
            'classroomId' => '1',
            'joinedType' => 'course',
            'userId' => '1',
            'orderId' => '1',
            'deadline' => '1',
            'levelId' => '1',
            'learnedNum' => '1',
            'credit' => '1',
            'noteNum' => '1',
            'noteLastUpdateTime' => '1',
            'isLearned' => '1',
            'finishedTime' => '1',
            'seq' => '1',
            'remark' => 'asdf',
            'isVisible' => '1',
            'role' => 'student',
            'locked' => '1',
            'deadlineNotified' => '1',
            'lastLearnTime' => '1',
            'courseSetId' => '1',
            'lastViewTime' => '0',
            'refundDeadline' => '0',
            'learnedCompulsoryTaskNum' => '0',
        );
    }

    private function mockCourse($fields = array())
    {
        $defaultFields = array(
            'courseSetId' => 1,
            'title' => 'varchar',
            'learnMode' => 'freeMode',
            'expiryMode' => 'days',
            'expiryDays' => 1,
            'expiryStartDate' => 1,
            'expiryEndDate' => 1,
            'summary' => 'text',
            'goals' => array('text'),
            'audiences' => array('text'),
            'isDefault' => 1,
            'maxStudentNum' => 10,
            'status' => 'published',
            'creator' => 1,
            'isFree' => 1,
            'price' => 1,
            'vipLevelId' => 1,
            'buyable' => 1,
            'tryLookable' => 1,
            'tryLookLength' => 1,
            'watchLimit' => 1,
            'services' => array('text'),
            'taskNum' => 1,
            'studentNum' => 1,
            'teacherIds' => array(1, 2),
            'parentId' => 0,
            'ratingNum' => 1,
            'rating' => 1,
            'noteNum' => 1,
            'buyExpiryTime' => 1,
            'threadNum' => 1,
            'type' => 'normal',
            'approval' => 1,
            'income' => 1,
            'originPrice' => 1,
            'coinPrice' => 1,
            'originCoinPrice' => 1,
            'showStudentNumType' => 'opened',
            'serializeMode' => 'none',
            'giveCredit' => 1, // 学完课程所有课时，可获得的总学分
            'about' => 'text', // 简介
            'locationId' => 1, // 上课地区ID
            'address' => 'varchar', // 上课地区地址
            'deadlineNotify' => 'none', // 开启有效期通知
            'daysOfNotifyBeforeDeadline' => 1,
            'useInClassroom' => 'single', // 课程能否用于多个班级
            'singleBuy' => 1, // 加入班级后课程能否单独购买
            'freeStartTime' => 1,
            'freeEndTime' => 2,
            'locked' => 1, // 是否上锁1上锁,0解锁
            'maxRate' => 1, // 最大抵扣百分比
            'cover' => 'varchar',
            'enableFinish' => 1, // 是否允许学院强制完成任务
        );

        $fields = array_merge($defaultFields, $fields);

        return $this->getCourseDao()->create($fields);
    }

    private function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }
}
