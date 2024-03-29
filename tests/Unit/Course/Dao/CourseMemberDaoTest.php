<?php

namespace Tests\Unit\Course\Dao;

use AppBundle\Common\ArrayToolkit;
use Tests\Unit\Base\BaseDaoTestCase;

class CourseMemberDaoTest extends BaseDaoTestCase
{
    public function testFindByIds()
    {
        $this->mockDataObject(['id' => 1, 'courseId' => 2]);
        $this->mockDataObject(['id' => 2, 'courseId' => 3]);
        $res = $this->getDao()->findByIds([1, 2]);
        $this->assertEquals('2', count($res));
    }

    public function testFindByCourseId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['userId' => 2]);
        $expected[] = $this->mockDataObject(['userId' => 3]);

        $values = [];
        foreach ($expected as $val) {
            $values[] = $val;
        }

        $res = [];
        $res[] = $this->getDao()->findByCourseId(1);
        $res[] = $this->getDao()->findByCourseId(2);

        $this->assertEquals($values, $res[0]);
        $this->assertEquals([], $res[1]);
    }

    public function testFindUserIdsByCourseId()
    {
        $this->mockDataObject(['userId' => 3, 'courseId' => 3]);
        $this->biz['user']['id'] = 3;
        $res = $this->getDao()->findUserIdsByCourseId(3);
        $this->assertEquals(3, $res[0]['userId']);
    }

    public function testFindByUserId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['courseId' => 2]);
        $expected[] = $this->mockDataObject(['courseId' => 3]);

        $values = [];
        foreach ($expected as $val) {
            $values[] = $val;
        }

        $res = [];
        $res[] = $this->getDao()->findByUserId(1);
        $res[] = $this->getDao()->findByUserId(2);

        $this->assertEquals($values, $res[0]);
        $this->assertEquals([], $res[1]);
    }

    public function testFindByCourseIds()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['courseId' => 2]);
        $expected[] = $this->mockDataObject(['userId' => 2, 'courseId' => 2]);

        $res = [];
        $res[] = $this->getDao()->findByCourseIds([1, 3]);
        $res[] = $this->getDao()->findByCourseIds([1, 2]);
        $res[] = $this->getDao()->findByCourseIds([3]);

        $this->assertEquals([$expected[0]], $res[0]);
        $this->assertEquals($expected, $res[1]);
        $this->assertEquals([], $res[2]);
    }

    public function testGetByCourseIdAndUserId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['courseId' => 2]);
        $expected[] = $this->mockDataObject(['userId' => 2]);

        $res = [];
        $res[] = $this->getDao()->getByCourseIdAndUserId(1, 1);
        $res[] = $this->getDao()->getByCourseIdAndUserId(2, 1);
        $res[] = $this->getDao()->getByCourseIdAndUserId(1, 2);

        foreach ($expected as $key => $val) {
            $this->assertEquals($val, $res[$key]);
        }
    }

    public function testFindLearnedByCourseIdAndUserId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['courseId' => 2]);
        $expected[] = $this->mockDataObject(['userId' => 2]);

        $res = [];
        $res[] = $this->getDao()->findLearnedByCourseIdAndUserId(1, 1);
        $res[] = $this->getDao()->findLearnedByCourseIdAndUserId(2, 1);
        $res[] = $this->getDao()->findLearnedByCourseIdAndUserId(1, 2);

        foreach ($expected as $key => $val) {
            $this->assertEquals([$val], $res[$key]);
        }
    }

    public function testFindByCourseIdAndRole()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['courseId' => 2]);
        $expected[] = $this->mockDataObject(['userId' => 2, 'role' => 'teacher']);

        $res = [];
        $res[] = $this->getDao()->findByCourseIdAndRole(1, 'student');
        $res[] = $this->getDao()->findByCourseIdAndRole(2, 'student');
        $res[] = $this->getDao()->findByCourseIdAndRole(1, 'teacher');

        foreach ($expected as $key => $val) {
            $this->assertEquals([$val], $res[$key]);
        }
    }

    public function testFindByCourseSetIdAndRole()
    {
        $expected = $this->mockDataObject(['courseSetId' => 3, 'role' => 'student']);
        $res = $this->getDao()->findByCourseSetIdAndRole(3, 'student');
        $this->assertEquals($expected, $res[0]);
    }

    public function testFindByUserIdAndJoinType()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['courseId' => 2, 'joinedType' => 'classroom']);
        $expected[] = $this->mockDataObject(['userId' => 2]);

        $res = [];
        $res[] = $this->getDao()->findByUserIdAndJoinType(1, 'course');
        $res[] = $this->getDao()->findByUserIdAndJoinType(1, 'classroom');
        $res[] = $this->getDao()->findByUserIdAndJoinType(2, 'course');

        foreach ($expected as $key => $val) {
            $this->assertEquals([$val], $res[$key]);
        }
    }

    public function testDeleteByCourseIdAndRole()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['courseId' => 2]);
        $expected[] = $this->mockDataObject(['userId' => 2, 'role' => 'teacher']);

        $this->getDao()->deleteByCourseIdAndRole(1, 'student');
        $this->getDao()->deleteByCourseIdAndRole(2, 'teacher');

        $res = [];
        $res[] = $this->getDao()->findByCourseIdAndRole(1, 'student');
        $res[] = $this->getDao()->findByCourseIdAndRole(2, 'student');
        $res[] = $this->getDao()->findByCourseIdAndRole(1, 'teacher');

        $this->assertEquals([], $res[0]);
        $this->assertEquals([$expected[1]], $res[1]);
        $this->assertEquals([$expected[2]], $res[2]);
    }

    public function testDeleteByCourseId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['courseId' => 2]);
        $expected[] = $this->mockDataObject(['userId' => 2]);

        $this->getDao()->deleteByCourseId(1);
        $this->getDao()->deleteByCourseId(3);

        $res = [];
        $res[] = $this->getDao()->findByCourseIds([1]);
        $res[] = $this->getDao()->findByCourseIds([2]);

        $this->assertEquals([], $res[0]);
        $this->assertEquals([$expected[1]], $res[1]);
    }

    public function testFindByUserIdAndCourseIds()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['courseId' => 2]);
        $expected[] = $this->mockDataObject(['userId' => 2]);

        $res = [];
        $res[] = $this->getDao()->findByUserIdAndCourseIds(1, [1]);
        $res[] = $this->getDao()->findByUserIdAndCourseIds(2, [1, 2]);
        $res[] = $this->getDao()->findByUserIdAndCourseIds(1, [1, 2]);

        $this->assertEquals([$expected[0]], $res[0]);
        $this->assertEquals([$expected[2]], $res[1]);
        $this->assertEquals([$expected[0], $expected[1]], $res[2]);
    }

    public function testCountLearningMembers()
    {
        $this->mockDataObject(['userId' => 3, 'learnedCompulsoryTaskNum' => 2, 'role' => 'student']);
        $this->mockCourse(['compulsoryTaskNum' => 3]);
        $conditions = [
            'm.userId' => 3,
            'm.role' => 'student',
        ];
        $res = $this->getDao()->countLearningMembers($conditions);
        $this->assertEquals(1, $res);
    }

    public function testFindLearningMembers()
    {
        $this->mockDataObject(['userId' => 3, 'learnedCompulsoryTaskNum' => 2, 'role' => 'student']);
        $this->mockCourse(['compulsoryTaskNum' => 3]);
        $conditions = [
            'm.userId' => 3,
            'm.role' => 'student',
        ];
        $res = $this->getDao()->findLearningMembers($conditions, 0, 1);
        $this->assertEquals(3, $res[0]['userId']);
    }

    public function testSearchMember()
    {
        $this->mockCourse();

        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['userId' => 2]);
        $expected[] = $this->mockDataObject(['courseId' => 2, 'role' => 'teacher']);

        $res1 = $this->getDao()->search([], [], 0, 10);
        $res2 = $this->getDao()->search(['userId' => 1], [], 0, 10);

        $this->assertEquals([$expected[0], $expected[1], $expected[2]], $res1);
        $this->assertEquals([$expected[0], $expected[2]], $res2);
    }

    public function testCountMember()
    {
        $this->mockCourse();

        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['userId' => 2]);
        $expected[] = $this->mockDataObject(['courseId' => 2, 'role' => 'teacher']);

        $res1 = $this->getDao()->count([], [], 0, 10);
        $res2 = $this->getDao()->count(['userId' => 1], [], 0, 10);

        $this->assertEquals(3, $res1);
        $this->assertEquals(2, $res2);
    }

    public function testSearchMemberCountGroupByFields()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['userId' => 2]);
        $expected[] = $this->mockDataObject(['courseId' => 2, 'role' => 'teacher']);

        $res = [];
        $res[] = $this->getDao()->searchMemberCountGroupByFields(['courseId' => 1], 'courseId', 0, 10);
        $res[] = $this->getDao()->searchMemberCountGroupByFields(
            ['courseId' => 1, 'userId' => 1],
            'courseId',
            0,
            10
        );

        $this->assertEquals([['courseId' => '1', 'count' => '2']], $res[0]);
        $this->assertEquals([['courseId' => '1', 'count' => '1']], $res[1]);
    }

    public function testFindByUserIdAndRole()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['userId' => 2]);
        $expected[] = $this->mockDataObject(['courseId' => 2, 'role' => 'teacher']);

        $res = [];
        $res[] = $this->getDao()->findByUserIdAndRole(1, 'student');
        $res[] = $this->getDao()->findByUserIdAndRole(2, 'student');
        $res[] = $this->getDao()->findByUserIdAndRole(1, 'teacher');

        foreach ($expected as $key => $val) {
            $this->assertEquals([$val], $res[$key]);
        }
    }

    public function testFindByUserIdAndCourseSetIdAndRole()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['userId' => 2, 'courseSetId' => 2]);
        $expected[] = $this->mockDataObject(['courseId' => 2, 'role' => 'teacher']);

        $res = [];
        $res[] = $this->getDao()->findByUserIdAndCourseSetIdAndRole(1, 1, 'student');
        $res[] = $this->getDao()->findByUserIdAndCourseSetIdAndRole(2, 2, 'student');
        $res[] = $this->getDao()->findByUserIdAndCourseSetIdAndRole(1, 1, 'teacher');

        foreach ($expected as $key => $val) {
            $this->assertEquals([$val], $res[$key]);
        }
    }

    public function testFindByConditionsGroupByUserId()
    {
        $this->mockDataObject(['userId' => 1, 'courseId' => 1, 'role' => 'student', 'courseSetId' => 2, 'locked' => 0, 'learnedCompulsoryTaskNum' => 2]);
        $this->mockDataObject(['userId' => 1, 'courseId' => 2, 'role' => 'student', 'courseSetId' => 2, 'locked' => 0, 'learnedCompulsoryTaskNum' => 3]);
        $this->mockDataObject(['userId' => 1, 'courseId' => 3, 'role' => 'student', 'courseSetId' => 2, 'locked' => 0, 'learnedCompulsoryTaskNum' => 4]);
        $res = $this->getDao()->findByConditionsGroupByUserId(
            [
                'role' => 'student',
                'courseSetId' => 2,
                'locked' => 0,
            ],
            ['createdTime' => 'DESC'],
            0,
            1
        );

        $this->assertEquals(4, $res[0]['learnedCompulsoryTaskNum']);
    }

    public function testFindMembersNotInClassroomByUserIdAndRole()
    {
        $this->mockCourse();

        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['userId' => 2]);
        $expected[] = $this->mockDataObject(['courseId' => 2, 'role' => 'teacher']);

        $res = [];
        $res[] = $this->getDao()->findMembersNotInClassroomByUserIdAndRole(1, 'student', 0, 10, false);
        $res[] = $this->getDao()->findMembersNotInClassroomByUserIdAndRole(2, 'student', 0, 10, false);
        $res[] = $this->getDao()->findMembersNotInClassroomByUserIdAndRole(2, 'teacher', 0, 10, false);

        $this->assertEquals([$expected[0]], $res[0]);
        $this->assertEquals([$expected[1]], $res[1]);
        $this->assertEquals([], $res[2]);
    }

    /**
     * @group current
     */
    public function testSearchMemberIds()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['userId' => 2, 'courseSetId' => 2]);
        $expected[] = $this->mockDataObject(['courseId' => 2, 'role' => 'teacher']);

        $res = $this->getDao()->searchMemberIds(['unique' => true], ['createdTime' => 'ASC'], 0, 10);

        $this->assertEquals(
            [['userId' => $expected[0]['userId']], ['userId' => $expected[1]['userId']]],
            $res
        );
    }

    public function testUpdateMembers()
    {
        $tmp = $this->getDefaultMockFields();
        $tmp['userId'] = '2';
        $tmp['courseId'] = '2';
        $tmp['role'] = 'teacher';

        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['userId' => 2]);
        $expected[] = $tmp;
        $expected[] = $this->mockDataObject(['courseId' => 2]);

        $this->getDao()->updateMembers(
            ['userId' => 1, 'courseId' => 2],
            ['userId' => 2, 'role' => 'teacher']
        );

        $res = [];
        $res[] = $this->getDao()->getByCourseIdAndUserId(1, 1);
        $res[] = $this->getDao()->getByCourseIdAndUserId(1, 2);
        $users = $this->getDao()->findByUserIdAndRole(2, 'teacher');
        $tmp = $users[0];
        unset($tmp['id']);
        unset($tmp['createdTime']);
        unset($tmp['updatedTime']);
        unset($tmp['stickyTime']);

        $res[] = $tmp;
        foreach ($res as $key => $val) {
            $this->assertEquals($expected[$key]['userId'], $val['userId']);
            $this->assertEquals($expected[$key]['courseId'], $val['courseId']);
        }
    }

    public function testCountUserLearnCourses()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['userId' => 2, 'courseSetId' => 2]);
        $expected[] = $this->mockDataObject(['courseId' => 2, 'role' => 'teacher']);

        $res = $this->getDao()->countUserLearnCourses(2);

        $this->assertEquals(1, $res);
    }

    public function testFindUserLearnCourseIds()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['userId' => 2, 'courseSetId' => 2]);
        $expected[] = $this->mockDataObject(['courseId' => 2, 'role' => 'teacher']);

        $res = $this->getDao()->findUserLearnCourseIds(2);

        $this->assertEquals(1, $res[0]['courseId']);
    }

    public function testCountLearnedMembers()
    {
        $this->mockDataObject(['userId' => 3, 'learnedCompulsoryTaskNum' => 3, 'role' => 'student']);
        $this->mockCourse(['compulsoryTaskNum' => 3]);
        $conditions = [
            'm.userId' => 3,
            'm.role' => 'student',
        ];
        $res = $this->getDao()->countLearnedMembers($conditions);

        $this->assertEquals(1, $res);
    }

    public function testFindLearnedMembers()
    {
        $this->mockDataObject(['userId' => 3, 'learnedCompulsoryTaskNum' => 3, 'role' => 'student']);
        $this->mockCourse(['compulsoryTaskNum' => 3]);
        $conditions = [
            'm.userId' => 3,
            'm.role' => 'student',
        ];
        $res = $this->getDao()->findLearnedMembers($conditions, 0, 1);
        $this->assertEquals(3, $res[0]['userId']);
    }

    public function testCountThreadsByCourseIdAndUserId()
    {
        $this->mockCourseThread(['courseId' => 1, 'userId' => 1, 'type' => 'discussion']);
        $this->mockCourseThread(['courseId' => 1, 'userId' => 1, 'type' => 'discussion']);
        $this->mockCourseThread(['courseId' => 1, 'userId' => 1, 'type' => 'discussion']);
        $res = $this->getDao()->countThreadsByCourseIdAndUserId(1, 1);

        $this->assertEquals(3, $res);
    }

    public function testCountActivitiesByCourseIdAndUserId()
    {
        $this->mockCourseTaskResult(['courseId' => 1, 'courseTaskId' => 1, 'userId' => 1, 'activityId' => 1]);
        $this->mockCourseTaskResult(['courseId' => 2, 'courseTaskId' => 2, 'userId' => 1, 'activityId' => 2]);
        $this->mockCourseTaskResult(['courseId' => 3, 'courseTaskId' => 3, 'userId' => 1, 'activityId' => 3]);
        $res = $this->getDao()->countActivitiesByCourseIdAndUserId(1, 1);

        $this->assertEquals(1, $res);
    }

    public function testCountPostsByCourseIdAndUserId()
    {
        $this->mockCourseThread(['id' => 3, 'courseId' => 1, 'userId' => 1, 'type' => 'discussion']);
        $this->mockCourseThreadPost(['userId' => 1, 'threadId' => 3]);
        $this->mockCourseThreadPost(['userId' => 1, 'threadId' => 3]);
        $res = $this->getDao()->countPostsByCourseIdAndUserId(1, 1);

        $this->assertEquals(2, $res);
    }

    public function testCountMemberNotInClassroomByUserIdAndCourseTypeAndIsLearned()
    {
        $this->mockCourse(['id' => 1, 'type' => 'normal', 'parentId' => 0]);
        $this->mockCourse(['id' => 2, 'type' => 'normal', 'parentId' => 0]);
        $this->mockDataObject(['userId' => 1, 'role' => 'student', 'courseId' => 1, 'isLearned' => 1]);
        $this->mockDataObject(['userId' => 1, 'role' => 'student', 'courseId' => 2, 'isLearned' => 1]);
        $res = $this->getDao()->countMemberNotInClassroomByUserIdAndCourseTypeAndIsLearned(1, 'student', 'normal', 1);
        $this->assertEquals(2, $res);
    }

    public function testCountMemberNotInClassroomByUserIdAndRoleAndIsLearned()
    {
        $this->mockCourse(['id' => 1, 'type' => 'normal', 'parentId' => 0]);
        $this->mockCourse(['id' => 2, 'type' => 'default', 'parentId' => 0]);
        $this->mockDataObject(['userId' => 1, 'role' => 'student', 'courseId' => 1, 'isLearned' => 1]);
        $this->mockDataObject(['userId' => 1, 'role' => 'student', 'courseId' => 2, 'isLearned' => 1]);
        $res = $this->getDao()->countMemberNotInClassroomByUserIdAndRoleAndIsLearned(1, 'student', 1);
        $this->assertEquals(2, $res);
    }

    public function testCountMemberNotInClassroomByUserIdAndRole()
    {
        $this->mockCourse(['id' => 1, 'type' => 'normal', 'parentId' => 0, 'status' => 'published']);
        $this->mockCourse(['id' => 2, 'type' => 'default', 'parentId' => 0, 'status' => 'published']);
        $this->mockDataObject(['userId' => 1, 'role' => 'student', 'courseId' => 1, 'isLearned' => 1]);
        $this->mockDataObject(['userId' => 1, 'role' => 'student', 'courseId' => 2, 'isLearned' => 1]);
        $res = $this->getDao()->countMemberNotInClassroomByUserIdAndRole(1, 'student', true);
        $this->assertEquals(2, $res);
    }

    public function testFindMembersNotInClassroomByUserIdAndCourseTypeAndIsLearned()
    {
        $this->mockCourse(['id' => 1, 'type' => 'normal', 'parentId' => 0, 'status' => 'published']);
        $this->mockCourse(['id' => 2, 'type' => 'normal', 'parentId' => 0, 'status' => 'published']);
        $this->mockDataObject(['userId' => 1, 'role' => 'student', 'courseId' => 1, 'isLearned' => 1]);
        $this->mockDataObject(['userId' => 1, 'role' => 'student', 'courseId' => 2, 'isLearned' => 1]);
        $res = $this->getDao()->findMembersNotInClassroomByUserIdAndCourseTypeAndIsLearned(1, 'student', 'normal', 1, 0, 2);
        $this->assertEquals(2, count($res));
    }

    public function testFindMembersNotInClassroomByUserIdAndRoleAndIsLearned()
    {
        $this->mockCourse(['id' => 1, 'type' => 'normal', 'parentId' => 0, 'status' => 'published']);
        $this->mockCourse(['id' => 2, 'type' => 'default', 'parentId' => 0, 'status' => 'published']);
        $this->mockDataObject(['userId' => 1, 'role' => 'student', 'courseId' => 1, 'isLearned' => 1]);
        $this->mockDataObject(['userId' => 1, 'role' => 'student', 'courseId' => 2, 'isLearned' => 1]);
        $res = $this->getDao()->findMembersNotInClassroomByUserIdAndRoleAndIsLearned(1, 'student', 1, 0, 2);
        $this->assertEquals(2, count($res));
    }

    public function testCountMemberByUserIdAndCourseTypeAndIsLearned()
    {
        $this->mockCourse(['id' => 1, 'type' => 'normal', 'parentId' => 0, 'status' => 'published']);
        $this->mockCourse(['id' => 2, 'type' => 'normal', 'parentId' => 0, 'status' => 'published']);
        $this->mockDataObject(['userId' => 1, 'role' => 'student', 'courseId' => 1, 'isLearned' => 1]);
        $this->mockDataObject(['userId' => 1, 'role' => 'student', 'courseId' => 2, 'isLearned' => 1]);
        $res = $this->getDao()->countMemberByUserIdAndCourseTypeAndIsLearned(1, 'student', 'normal', 1);
        $this->assertEquals(2, $res);
    }

    public function testCountMemberByUserIdAndRoleAndIsLearned()
    {
        $this->mockCourse(['id' => 1, 'type' => 'normal', 'parentId' => 0, 'status' => 'published']);
        $this->mockCourse(['id' => 2, 'type' => 'normal', 'parentId' => 0, 'status' => 'published']);
        $this->mockDataObject(['userId' => 1, 'role' => 'student', 'courseId' => 1, 'isLearned' => 1]);
        $this->mockDataObject(['userId' => 1, 'role' => 'student', 'courseId' => 2, 'isLearned' => 1]);
        $res = $this->getDao()->countMemberByUserIdAndRoleAndIsLearned(1, 'student', 1);
        $this->assertEquals(2, $res);
    }

    public function testFindMembersNotInClassroomByUserIdAndRoleAndType()
    {
        $this->mockCourse(['id' => 1, 'type' => 'normal', 'parentId' => 0, 'status' => 'published']);
        $this->mockCourse(['id' => 2, 'type' => 'normal', 'parentId' => 0, 'status' => 'published']);
        $this->mockCourse(['id' => 3, 'type' => 'normal', 'parentId' => 0, 'status' => 'published']);
        $this->mockDataObject(['userId' => 1, 'role' => 'student', 'courseId' => 1, 'isLearned' => 1]);
        $this->mockDataObject(['userId' => 1, 'role' => 'student', 'courseId' => 2, 'isLearned' => 1]);
        $res = $this->getDao()->findMembersNotInClassroomByUserIdAndRoleAndType(1, 'student', 'normal', 0, 2, true);

        $this->assertEquals(2, count($res));
    }

    public function testFindByUserIdAndClassroomId()
    {
        $this->mockDataObject(['id' => 1, 'userId' => 1, 'role' => 'student', 'courseId' => 1, 'classroomId' => 1]);
        $this->mockDataObject(['id' => 2, 'userId' => 2, 'role' => 'student', 'courseId' => 1, 'classroomId' => 1]);
        $this->mockDataObject(['id' => 3, 'userId' => 1, 'role' => 'student', 'courseId' => 2, 'classroomId' => 1]);
        $this->mockDataObject(['id' => 4, 'userId' => 2, 'role' => 'student', 'courseId' => 2, 'classroomId' => 1]);
        $this->mockDataObject(['id' => 5, 'userId' => 1, 'role' => 'student', 'courseId' => 3, 'classroomId' => 2]);

        $members1 = ArrayToolkit::index($this->getDao()->findByUserIdAndClassroomId(1, 1), 'id');
        $members2 = ArrayToolkit::index($this->getDao()->findByUserIdAndClassroomId(2, 1), 'id');
        $members3 = ArrayToolkit::index($this->getDao()->findByUserIdAndClassroomId(1, 2), 'id');

        self::assertCount(2, $members1);
        self::assertArrayHasKey(1, $members1);
        self::assertArrayHasKey(3, $members1);

        self::assertCount(2, $members2);
        self::assertArrayHasKey(2, $members2);
        self::assertArrayHasKey(4, $members2);

        self::assertCount(1, $members3);
        self::assertArrayHasKey(5, $members3);
    }

    public function testFindByUserIdsAndClassroomId()
    {
        $this->mockDataObject(['id' => 6, 'userId' => 1, 'role' => 'student', 'courseId' => 1, 'classroomId' => 1]);
        $this->mockDataObject(['id' => 7, 'userId' => 2, 'role' => 'student', 'courseId' => 1, 'classroomId' => 1]);
        $this->mockDataObject(['id' => 8, 'userId' => 1, 'role' => 'student', 'courseId' => 2, 'classroomId' => 1]);
        $this->mockDataObject(['id' => 9, 'userId' => 2, 'role' => 'student', 'courseId' => 2, 'classroomId' => 1]);
        $this->mockDataObject(['id' => 10, 'userId' => 1, 'role' => 'student', 'courseId' => 3, 'classroomId' => 2]);
        $this->mockDataObject(['id' => 11, 'userId' => 2, 'role' => 'student', 'courseId' => 3, 'classroomId' => 2]);
        $this->mockDataObject(['id' => 12, 'userId' => 3, 'role' => 'student', 'courseId' => 3, 'classroomId' => 2]);
        $members1 = ArrayToolkit::index($this->getDao()->findByUserIdsAndClassroomId([1, 2], 1), 'id');
        $members2 = ArrayToolkit::index($this->getDao()->findByUserIdsAndClassroomId([1, 2], 2), 'id');

        self::assertCount(4, $members1);
        self::assertArrayHasKey(6, $members1);
        self::assertArrayHasKey(7, $members1);
        self::assertArrayHasKey(8, $members1);
        self::assertArrayHasKey(9, $members1);

        self::assertCount(2, $members2);
        self::assertArrayHasKey(10, $members2);
        self::assertArrayHasKey(11, $members2);
    }

    public function testFindMultiClassIdsByUserId()
    {
        $this->mockDataObject(['userId' => 1, 'courseId' => 1, 'role' => 'teacher', 'courseSetId' => 1, 'locked' => 0, 'multiClassId' => 1]);
        $this->mockDataObject(['userId' => 1, 'courseId' => 2, 'role' => 'teacher', 'courseSetId' => 2, 'locked' => 0, 'multiClassId' => 3]);
        $this->mockDataObject(['userId' => 2, 'courseId' => 3, 'role' => 'teacher', 'courseSetId' => 3, 'locked' => 0, 'multiClassId' => 4]);

        $result = $this->getDao()->findMultiClassIdsByUserId(1);

        self::assertCount(2, $result);
    }

    public function testCountGroupByCourseId()
    {
        $this->mockDataObject(['userId' => 1, 'courseId' => 1, 'role' => 'student', 'courseSetId' => 1, 'locked' => 0, 'multiClassId' => 1]);
        $this->mockDataObject(['userId' => 1, 'courseId' => 2, 'role' => 'teacher', 'courseSetId' => 2, 'locked' => 0, 'multiClassId' => 3]);
        $this->mockDataObject(['userId' => 2, 'courseId' => 1, 'role' => 'student', 'courseSetId' => 1, 'locked' => 0, 'multiClassId' => 1]);

        $result = $this->getDao()->countGroupByCourseId(['multiClassId' => 1, 'role' => 'student']);

        $this->assertEquals(2, $result[0]['count']);
    }

    protected function getDefaultMockFields()
    {
        return [
            'courseId' => '1',
            'classroomId' => '1',
            'joinedType' => 'course',
            'joinedChannel' => '',
            'userId' => '1',
            'orderId' => '1',
            'deadline' => '1',
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
            'learnedElectiveTaskNum' => '0',
            'startLearnTime' => '0',
        ];
    }

    private function mockCourse($fields = [])
    {
        $defaultFields = [
            'courseSetId' => 1,
            'title' => 'varchar',
            'learnMode' => 'freeMode',
            'expiryMode' => 'days',
            'expiryDays' => 1,
            'expiryStartDate' => 1,
            'expiryEndDate' => 1,
            'summary' => 'text',
            'goals' => ['text'],
            'audiences' => ['text'],
            'isDefault' => 1,
            'maxStudentNum' => 10,
            'status' => 'published',
            'creator' => 1,
            'isFree' => 1,
            'price' => 1,
            'buyable' => 1,
            'tryLookable' => 1,
            'tryLookLength' => 1,
            'watchLimit' => 1,
            'services' => ['text'],
            'taskNum' => 1,
            'studentNum' => 1,
            'teacherIds' => [1, 2],
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
        ];

        $fields = array_merge($defaultFields, $fields);

        return $this->getCourseDao()->create($fields);
    }

    protected function mockCourseThread($fields = [])
    {
        $defaultFields = [
            'courseId' => 1,
            'taskId' => 1,
            'userId' => 1,
            'type' => 'discussion',
            'title' => 'course thread title',
            'content' => 'course thread content',
            'courseSetId' => 1,
        ];

        $fields = array_merge($defaultFields, $fields);

        return $this->getThreadDao()->create($fields);
    }

    protected function mockCourseTaskResult($fields = [])
    {
        $defaultFields = [
            'activityId' => 1,
            'courseId' => 1,
            'courseTaskId' => 1,
            'userId' => 1,
            'status' => 'finished',
        ];

        $fields = array_merge($defaultFields, $fields);

        return $this->getTaskResultDao()->create($fields);
    }

    private function mockCourseThreadPost($fields = [])
    {
        $defaultFields = [
            'courseId' => 1,
            'taskId' => 1,
            'threadId' => 1,
            'userId' => 1,
            'content' => 'post content',
        ];

        $fields = array_merge($defaultFields, $fields);

        return $this->getThreadPostDao()->create($fields);
    }

    private function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }

    private function getThreadDao()
    {
        return $this->createDao('Course:ThreadDao');
    }

    private function getTaskResultDao()
    {
        return $this->createDao('Task:TaskResultDao');
    }

    private function getThreadPostDao()
    {
        return $this->createDao('Course:ThreadPostDao');
    }
}
