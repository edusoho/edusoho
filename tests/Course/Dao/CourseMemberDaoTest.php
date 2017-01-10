<?php

namespace Tests\Course\Dao;

use Tests\Base\BaseDaoTestCase;

class CourseMemberDaoTest extends BaseDaoTestCase
{
    public function testFindByCourseId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('userId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 3));

        $ids = array();
        foreach ($factor as $val) {
            $ids[] = current($val);
        }

        $res = array();
        $res[] = $this->getDao()->findByCourseId(1);
        $res[] = $this->getDao()->findByCourseId(2);

        $this->assertEquals($ids, $res[0]);
        $this->assertEquals(array(), $res[1]);
    }

    public function testFindByUserId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('courseId' => 2));
        $factor[] = $this->mockDataObject(array('courseId' => 3));

        $ids = array();
        foreach ($factor as $val) {
            $ids[] = current($val);
        }

        $res = array();
        $res[] = $this->getDao()->findByUserId(1);
        $res[] = $this->getDao()->findByUserId(2);

        $this->assertEquals($ids, $res[0]);
        $this->assertEquals(array(), $res[1]);
    }

    public function testFindByCourseIds()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('courseId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 2, 'courseId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByCourseIds(array(1, 3));
        $res[] = $this->getDao()->findByCourseIds(array(1, 2));
        $res[] = $this->getDao()->findByCourseIds(array(3));

        $this->assertEquals(array($factor[0]), $res[0]);
        $this->assertEquals($factor, $res[1]);
        $this->assertEquals(array(), $res[2]);
    }

    public function testGetByCourseIdAndUserId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('courseId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->getByCourseIdAndUserId(1, 1);
        $res[] = $this->getDao()->getByCourseIdAndUserId(2, 1);
        $res[] = $this->getDao()->getByCourseIdAndUserId(1, 2);

        foreach ($factor as $key => $val) {
            $this->assertEquals($val, $res[$key]);
        }
    }

    // Todo
    public function testFindLearnedByCourseIdAndUserId()
    {
        ;
    }

    public function testFindByCourseIdAndRole()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('courseId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 2, 'role' => 'teacher'));

        $res = array();
        $res[] = $this->getDao()->findByCourseIdAndRole(1, 'student');
        $res[] = $this->getDao()->findByCourseIdAndRole(2, 'student');
        $res[] = $this->getDao()->findByCourseIdAndRole(1, 'teacher');

        foreach ($factor as $key => $val) {
            $this->assertEquals(array($val), $res[$key]);
        }
    }

    public function testFindByUserIdAndJoinType()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('courseId' => 2, 'joinedType' => 'classroom'));
        $factor[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByUserIdAndJoinType(1, 'course');
        $res[] = $this->getDao()->findByUserIdAndJoinType(1, 'classroom');
        $res[] = $this->getDao()->findByUserIdAndJoinType(2, 'course');

        foreach ($factor as $key => $val) {
            $this->assertEquals(array($val), $res[$key]);
        }
    }

    public function testDeleteByCourseIdAndRole()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('courseId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 2, 'role' => 'teacher'));

        $this->getDao()->deleteByCourseIdAndRole(1, 'student');
        $this->getDao()->deleteByCourseIdAndRole(2, 'teacher');

        $res = array();
        $res[] = $this->getDao()->findByCourseIdAndRole(1, 'student');
        $res[] = $this->getDao()->findByCourseIdAndRole(2, 'student');
        $res[] = $this->getDao()->findByCourseIdAndRole(1, 'teacher');

        $this->assertEquals(array(), $res[0]);
        $this->assertEquals(array($factor[1]), $res[1]);
        $this->assertEquals(array($factor[2]), $res[2]);
    }

    public function testDeleteByCourseId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('courseId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 2));

        $this->getDao()->deleteByCourseId(1);
        $this->getDao()->deleteByCourseId(3);

        $res = array();
        $res[] = $this->getDao()->findByCourseIds(array(1));
        $res[] = $this->getDao()->findByCourseIds(array(2));

        $this->assertEquals(array(), $res[0]);
        $this->assertEquals(array($factor[1]), $res[1]);
    }

    public function testFindByUserIdAndCourseIds()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('courseId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByUserIdAndCourseIds(1, array(1));
        $res[] = $this->getDao()->findByUserIdAndCourseIds(2, array(1, 2));
        $res[] = $this->getDao()->findByUserIdAndCourseIds(1, array(1, 2));

        $this->assertEquals(array($factor[0]), $res[0]);
        $this->assertEquals(array($factor[2]), $res[1]);
        $this->assertEquals(array($factor[0], $factor[1]), $res[2]);
    }

    // Todo
    public function testSearchMemberFetchCourse()
    {
        ;
    }

    // Todo
    public function testCountMemberFetchCourse()
    {
        ;
    }

    // Todo
    public function testSearchMemberCountGroupByFields()
    {
        ;
    }

    public function testFindByUserIdAndRole()
    {
        ;
    }

    public function testFindMembersNotInClassroomByUserIdAndRole()
    {
        ;
    }

    public function testSearchMemberIds()
    {
        ;
    }

    public function testUpdateMembers()
    {
        ;
    }

    public function testCountThreadsByCourseIdAndUserId()
    {
        ;
    }

    public function testCountActivitiesByCourseIdAndUserId()
    {
        ;
    }

    public function testCountPostsByCourseIdAndUserId()
    {
        ;
    }

    protected function getDefaultMockFields()
    {
        return array(
            'courseId' => 1,
            'classroomId' => 1,
            'joinedType' => 'course',
            'userId' => 1,
            'orderId' => 1,
            'deadline' => 1,
            'levelId' => 1,
            'learnedNum' => 1,
            'credit' => 1,
            'noteNum' => 1,
            'noteLastUpdateTime' => 1,
            'isLearned' => 1,
            'finishedTime' => 1,
            'seq' => 1,
            'remark' => 'asdf',
            'isVisible' => 1,
            'role' => 'student',
            'locked' => 1,
            'deadlineNotified' => 1,
            'lastLearnTime' => 1
        );
    }
}
