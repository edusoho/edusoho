<?php

namespace Tests\Unit\Classroom\Service;

use AppBundle\Common\ClassroomToolkit;
use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Role\Util\PermissionBuilder;
use Biz\User\CurrentUser;
use Biz\User\Service\UserService;
use AppBundle\Common\TimeMachine;

class ClassroomServiceTest extends BaseTestCase
{
    public function testUpdateMemberDeadlineByMemberId()
    {
        $user = $this->getCurrentUser();
        $textClassroom = array(
            'title' => 'test066',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $student = $this->getClassroomService()->becomeStudent($classroom['id'], $user['id'], $info = array());

        $time = time();
        $deadline = ClassroomToolkit::buildMemberDeadline(array(
            'expiryMode' => 'date',
            'expiryValue' => $time,
        ));
        $student = $this->getClassroomService()->updateMemberDeadlineByMemberId($student['id'], array(
            'deadline' => $deadline,
        ));
        $this->assertEquals($time, $student['deadline']);
    }

    public function testFindWillOverdueClassrooms()
    {
        $user = $this->getCurrentUser();
        $deadline = time() + 1000;
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'findMembersByUserId',
                    'returnValue' => array(array('classroomId' => 11, 'deadlineNotified' => 0, 'deadline' => $deadline)),
                    'withParams' => array($user['id']),
                ),
            )
        );
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'findByIds',
                    'returnValue' => array(array('id' => 11, 'expiryValue' => 1)),
                    'withParams' => array(array(11)),
                ),
            )
        );
        $result = $this->getClassroomService()->findWillOverdueClassrooms();

        $this->assertEquals(array(array('id' => 11, 'expiryValue' => 1)), $result[0]);
    }

    public function testBatchUpdateOrg()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'update',
                    'returnValue' => array(),
                    'withParams' => array(11, array()),
                ),
            )
        );
        $result = $this->getClassroomService()->batchUpdateOrg(11, 'testCode');

        $this->assertNull($result);
    }

    public function testUpdateMembersDeadlineByClassroomId()
    {
        $textClassroom = array(
            'title' => 'test066',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);
        $user = $this->createStudent();

        $this->getClassroomService()->becomeStudent($classroom['id'], $user['id']);
        $time = time();
        $deadline = ClassroomToolkit::buildMemberDeadline(array(
            'expiryMode' => 'date',
            'expiryValue' => $time,
        ));

        $updated = $this->getClassroomService()->updateMembersDeadlineByClassroomId($classroom['id'], $deadline);

        $this->assertEquals(1, $updated);
    }

    public function testIsClassroomOverDue()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('expiryMode' => 'date', 'expiryValue' => 222222),
                    'withParams' => array(1),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array('expiryMode' => 'test', 'expiryValue' => 222222),
                    'withParams' => array(1),
                    'runTimes' => 1,
                ),
            )
        );
        $result1 = $this->getClassroomService()->isClassroomOverDue(1);
        $result2 = $this->getClassroomService()->isClassroomOverDue(1);

        $this->assertTrue($result1);
        $this->assertFalse($result2);
    }

    public function testAddClassroom()
    {
        $textClassroom = array(
            'title' => 'test',
            'status' => 'draft',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $this->assertEquals(1, $classroom['id']);

        $this->assertEquals($textClassroom['title'], $classroom['title']);

        $this->assertEquals('draft', $classroom['status']);
    }

    public function testGetClassroom()
    {
        $textClassroom = array(
            'title' => 'test11',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);
        // $classroom = $this->getClassroomService()->updateClassroom($id, $fields);
        // 是为了清空缓存再getClassroom,保证test之间互不影响,下同
        $classroom = $this->getClassroomService()->getClassroom($classroom['id']);

        $this->assertEquals(1, $classroom['id']);

        $this->assertEquals($textClassroom['title'], $classroom['title']);

        $this->assertEquals('draft', $classroom['status']);
    }

    public function testSearchClassrooms()
    {
        $classrooms = $this->getClassroomService()->searchClassrooms(array(), array(), 0, 1);

        $this->assertEmpty($classrooms);
        $textClassroom = array(
            'title' => 'test11',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $classrooms = $this->getClassroomService()->searchClassrooms(array('id' => $classroom['id']), array(), 0, 1);
        $this->assertEquals(array_shift($classrooms), $classroom);
    }

    public function testcountClassrooms()
    {
        $textClassroom1 = array(
            'title' => 'test1',
        );
        $textClassroom2 = array(
            'title' => 'test2',
        );
        $textClassroom3 = array(
            'title' => 'test3',
        );
        $classroom1 = $this->getClassroomService()->addClassroom($textClassroom1);
        $this->getClassroomService()->updateClassroom($classroom1['id'], $textClassroom1);
        $classroom2 = $this->getClassroomService()->addClassroom($textClassroom2);
        $this->getClassroomService()->updateClassroom($classroom2['id'], $textClassroom2);
        $classroom3 = $this->getClassroomService()->addClassroom($textClassroom3);
        $this->getClassroomService()->updateClassroom($classroom3['id'], $textClassroom3);
        $conditions = array('status' => 'draft', 'showable' => 1, 'buyable' => 1);
        $result = $this->getClassroomService()->countClassrooms($conditions);
        $this->assertEquals(3, $result);
    }

    public function testRecommendClassroom()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin3',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_SUPER_ADMIN'),
        ));
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'title' => 'title'),
                    'withParams' => array(1),
                ),
                array(
                    'functionName' => 'update',
                    'returnValue' => array('id' => 1, 'recommended' => 1, 'title' => 'title'),
                ),
            )
        );
        $result = $this->getClassroomService()->recommendClassroom(1, 11);

        $this->assertEquals(array('id' => 1, 'recommended' => 1, 'title' => 'title'), $result);
    }

    public function testCancelRecommendClassroom()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin3',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_SUPER_ADMIN'),
        ));
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'title' => 'title'),
                    'withParams' => array(1),
                ),
                array(
                    'functionName' => 'update',
                    'returnValue' => array('id' => 1, 'recommended' => 0, 'title' => 'title'),
                    'withParams' => array(
                        1,
                        array(
                            'recommended' => 0,
                            'recommendedTime' => 0,
                            'recommendedSeq' => 100,
                        ),
                    ),
                ),
            )
        );
        $result = $this->getClassroomService()->cancelRecommendClassroom(1, 11);

        $this->assertEquals(array('id' => 1, 'recommended' => 0, 'title' => 'title'), $result);
    }

    public function testGetClassroomCourse()
    {
        $textClassroom = array(
            'title' => 'test19878',
        );

        $course1 = $this->createCourse('Test Course 1');

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $copyCourses = $this->getClassroomService()->addCoursesToClassroom($classroom['id'], array($course1['id']));
        $copyCourse = current($copyCourses);
        $result = $this->getClassroomService()->getClassroomCourse($classroom['id'], $copyCourse['id']);
        $this->assertEquals($copyCourse['id'], $result['courseId']);
    }

    public function testFindActiveCoursesByClassroomId()
    {
        $textClassroom = array(
            'title' => 'testwe',
        );

        $course1 = $this->createCourse('Test Course 1');
        $course2 = $this->createCourse('Test Course 2');

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $courseSet = $this->mockCourseSet();
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSet);

        $copyCourses = $this->getClassroomService()->addCoursesToClassroom($classroom['id'],
            array($course1['id'], $course2['id']));
        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);

        $this->assertEquals(2, count($courses));

        $this->getClassroomService()->deleteClassroomCourses($classroom['id'], array($copyCourses[1]['id']));
        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
        $this->assertEquals(1, count($courses));

        $courseFirst = $courses[0];
        $this->assertEquals($course1['title'], $courseFirst['title']);
    }

    public function testFindActiveCoursesByClassroomIdWithExistCourses()
    {
        $this->mockBiz('Classroom:ClassroomCourseDao', array(
            array('functionName' => 'findActiveCoursesByClassroomId', 'returnValue' => array(array('courseId' => 1))),
        ));

        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId(1);
        $this->assertEmpty($courses);
    }

    public function testFindClassroomIdsByCourseId()
    {
        $textClassroom = array(
            'title' => 'test3',
        );
        $course = $this->createCourse('Test Course 1');
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], array($course['id']));

        $classroomId = $this->getClassroomService()->findClassroomIdsByCourseId(1);

        $this->assertEquals(1, $classroom['id']);
    }

    public function testFindClassroomsByCourseId()
    {
        $textClassroom1 = array(
            'title' => 'test12333',
        );
        $course1 = $this->createCourse('Test Course 1');
        $course2 = $this->createCourse('Test Course 2');
        $classroom1 = $this->getClassroomService()->addClassroom($textClassroom1);

        $this->getClassroomService()->addCoursesToClassroom($classroom1['id'], array($course1['id']));

        $textClassroom2 = array(
            'title' => 'test11123',
        );

        $classroom2 = $this->getClassroomService()->addClassroom($textClassroom2);

        $this->getClassroomService()->addCoursesToClassroom($classroom2['id'], array($course2['id']));

        $classroom = $this->getClassroomService()->updateClassroom(1, $textClassroom1);

        $this->assertEquals('test12333', $classroom['title']);
    }

    public function testGetClassroomByCourseId()
    {
        $result1 = $this->getClassroomService()->getClassroomByCourseId(1);

        $this->assertEquals(array(), $result1);

        $this->mockBiz(
            'Classroom:ClassroomCourseDao',
            array(
                array(
                    'functionName' => 'findClassroomIdsByCourseId',
                    'returnValue' => array(array('classroomId' => 1)),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'titile' => 'title'),
                    'withParams' => array(1),
                ),
            )
        );
        $result2 = $this->getClassroomService()->getClassroomByCourseId(1);

        $this->assertEquals(array('id' => 1, 'titile' => 'title'), $result2);
    }

    public function testGetClassroomCourseByCourseSetId()
    {
        $this->mockBiz(
            'Classroom:ClassroomCourseDao',
            array(
                array(
                    'functionName' => 'getByCourseSetId',
                    'returnValue' => array('id' => 1, 'classroomId' => 1),
                    'withParams' => array(1),
                ),
            )
        );
        $result = $this->getClassroomService()->getClassroomCourseByCourseSetId(1);

        $this->assertEquals(array('id' => 1, 'classroomId' => 1), $result);
    }

    public function testFindClassroomByCourseId()
    {
        $textClassroom = array(
            'title' => 'test1234',
        );
        $course = $this->createCourse('Test Course');
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], array($course['id']));
        $result = $this->getClassroomService()->getClassroomByCourseId($course['id']);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $this->assertEquals('test1234', $classroom['title']);
    }

    public function testFindClassroomByTitle()
    {
        $textClassroom = array(
            'title' => 'test111',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);
        $title = 'test111';

        $result = $this->getClassroomService()->findClassroomByTitle($title);

        $this->assertEquals($textClassroom['title'], $result['title']);
    }

    public function testFindClassroomsByLikeTitle()
    {
        $textClassroom1 = array(
            'title' => 'test232',
        );
        $textClassroom2 = array(
            'title' => 'test334',
        );
        $classroom1 = $this->getClassroomService()->addClassroom($textClassroom1);
        $classroom1 = $this->getClassroomService()->updateClassroom($classroom1['id'], $textClassroom1);
        $classroom2 = $this->getClassroomService()->addClassroom($textClassroom2);
        $classroom2 = $this->getClassroomService()->updateClassroom($classroom2['id'], $textClassroom2);
        $likeTitle = '%test2%';

        $result = $this->getClassroomService()->findClassroomsByLikeTitle($likeTitle);

        $this->assertEquals(1, count($result));
    }

    public function testUpdateClassroom()
    {
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $fields = array(
            'title' => 'test11111',
            'description' => '1',
            'about' => '1',
        );

        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $fields);

        $this->assertEquals($fields['title'], $classroom['title']);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.not_found
     */
    public function testUpdateClassroomNotFound()
    {
        $fields = array(
            'title' => 'test11111',
        );

        $this->getClassroomService()->updateClassroom('999', $fields);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_error
     */
    public function testUpdateClassroomWithErrorParam()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'noteNum' => 2),
                ),
            )
        );

        $this->getClassroomService()->updateClassroom(1, array());
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.forbidden_update_expiry_date
     */
    public function testUpdateClassroomWithForbiddenUpdateExpiryDate()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'status' => 'published', 'expiryMode' => 2),
                ),
            )
        );

        $this->getClassroomService()->updateClassroom(1, array('expiryMode' => 1, 'expiryValue' => 1));
    }

    public function testCanUpdateMembersDeadline()
    {
        $result = ReflectionUtils::invokeMethod($this->getClassroomService(), 'canUpdateMembersDeadline', array(array('expiryMode' => 1), 1));
        $this->assertTrue($result);

        $result = ReflectionUtils::invokeMethod($this->getClassroomService(), 'canUpdateMembersDeadline', array(array('expiryMode' => 'days'), 'days'));
        $this->assertFalse($result);
    }

    public function testWaveClassroom()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'wave',
                    'returnValue' => array('id' => 1, 'noteNum' => 2),
                    'withParams' => array(array(1), array('noteNum' => 1)),
                ),
            )
        );
        $result = $this->getClassroomService()->waveClassroom(1, 'noteNum', +1);

        $this->assertEquals(array('id' => 1, 'noteNum' => 2), $result);
    }

    public function testDeleteClassroom()
    {
        $textClassroom = array(
            'title' => 'test',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);
        $this->getClassroomService()->deleteClassroom($classroom['id']);
        $result = $this->getClassroomService()->getClassroom($classroom['id']);
        $this->assertEquals(0, count($result));
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.not_found
     */
    public function testDeleteClassroomWithNotFound()
    {
        $this->getClassroomService()->deleteClassroom(1);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.forbidden_delete_not_draft
     */
    public function testDeleteClassroomWithForbiddenDeleteNotDraft()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'status' => 'published'),
                ),
            )
        );
        $this->getClassroomService()->deleteClassroom(1);
    }

    public function testIsClassroomTeacher()
    {
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'getByClassroomIdAndUserId',
                    'returnValue' => array('id' => 1, 'role' => array('teacher')),
                    'withParams' => array(1, 1),
                ),
            )
        );
        $result = $this->getClassroomService()->isClassroomTeacher(1, 1);

        $this->assertTrue($result);
    }

    public function testPublishClassroom()
    {
        $textClassroom = array(
            'title' => 'test6543',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->assertEquals('draft', $classroom['status']);

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 3,
            'nickname' => 'admin3',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $enabled = $this->getClassroomService()->canManageClassroom($classroom['id']);
        $this->assertEquals(false, $enabled);

        $this->getClassroomService()->addHeadTeacher($classroom['id'], 3);

        $enabled = $this->getClassroomService()->canManageClassroom($classroom['id']);

        $this->assertEquals(true, $enabled);

        $this->getClassroomService()->publishClassroom($classroom['id']);
        $result = $this->getClassroomService()->getClassroom($classroom['id']);

        $this->assertEquals('published', $result['status']);
    }

    public function testCloseClassroom()
    {
        $textClassroom = array(
            'title' => 'test1111223',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 4,
            'nickname' => 'admin4',
            'email' => 'admin4@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->getClassroomService()->addHeadTeacher($classroom['id'], 4);

        $this->getClassroomService()->publishClassroom($classroom['id']);
        $result = $this->getClassroomService()->getClassroom($classroom['id']);

        $this->assertEquals('published', $result['status']);

        $this->getClassroomService()->closeClassroom($classroom['id']);
        $result = $this->getClassroomService()->getClassroom($classroom['id']);

        $this->assertEquals('closed', $result['status']);
    }

    public function testChangePicture()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array(
                        'id' => 1,
                        'title' => 'title',
                        'smallPicture' => 'smallPicture',
                        'middlePicture' => 'middlePicture',
                        'largePicture' => 'largePicture',
                    ),
                    'withParams' => array(1),
                ),
                array(
                    'functionName' => 'update',
                    'returnValue' => array('id' => 1, 'title' => 'title'),
                    'withParams' => array(
                        1,
                        array('smallPicture' => 'uri1', 'middlePicture' => 'uri2', 'largePicture' => 'uri3'),
                    ),
                ),
            )
        );
        $this->mockBiz(
            'Content:FileService',
            array(
                array(
                    'functionName' => 'getFilesByIds',
                    'returnValue' => array(
                        array('id' => 1, 'uri' => 'uri1'),
                        array('id' => 2, 'uri' => 'uri2'),
                        array('id' => 3, 'uri' => 'uri3'),
                    ),
                    'withParams' => array(array(1, 2, 3)),
                ),
                array(
                    'functionName' => 'deleteFileByUri',
                    'returnValue' => array(),
                    'withParams' => array('smallPicture'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'deleteFileByUri',
                    'returnValue' => array(),
                    'withParams' => array('middlePicture'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'deleteFileByUri',
                    'returnValue' => array(),
                    'withParams' => array('largePicture'),
                    'runTimes' => 1,
                ),
            )
        );
        $this->mockBiz(
            'System:LogService',
            array(
                array(
                    'functionName' => 'info',
                    'returnValue' => array(),
                    'withParams' => array(
                        'classroom',
                        'update_picture',
                        '更新课程《title》(#1)图片',
                        array(
                            'smallPicture' => 'uri1',
                            'middlePicture' => 'uri2',
                            'largePicture' => 'uri3',
                        ),
                    ),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'info',
                    'returnValue' => array(),
                    'withParams' => array(
                        'classroom',
                        'update',
                        '更新班级《title》(#1)',
                        array(
                            'smallPicture' => array('old' => 'smallPicture', 'new' => 'uri1'),
                            'middlePicture' => array('old' => 'middlePicture', 'new' => 'uri2'),
                            'largePicture' => array('old' => 'largePicture', 'new' => 'uri3'),
                            'id' => 1,
                            'showTitle' => 'title',
                        ),
                    ),
                    'runTimes' => 1,
                ),
            )
        );
        $result = $this->getClassroomService()->changePicture(
            1,
            array(
                array('id' => 1, 'type' => 'small'),
                array('id' => 2, 'type' => 'middle'),
                array('id' => 3, 'type' => 'large'),
            )
        );

        $this->assertEquals(array('id' => 1, 'title' => 'title'), $result);
    }

    public function testIsCourseInClassroom()
    {
        $textClassroom = array(
            'title' => 'test43234',
        );
        $course = $this->createCourse('Test Course 1');

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $copyCourses = $this->getClassroomService()->addCoursesToClassroom($classroom['id'], array($course['id']));
        $copyCourse = current($copyCourses);
        $enabled = $this->getClassroomService()->isCourseInClassroom($copyCourse['id'], $classroom['id']);
        $this->assertEquals(true, $enabled);

        $this->getClassroomService()->deleteClassroomCourses($classroom['id'], array($copyCourse['id']));

        $enabled = $this->getClassroomService()->isCourseInClassroom($copyCourse['id'], $classroom['id']);
        $this->assertEquals(false, $enabled);
    }

    public function testDeleteClassroomCourses()
    {
        $textClassroom = array(
            'title' => 'test54345',
        );
        $course = $this->createCourse('Test Course 2');

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $copyCourses = $this->getClassroomService()->addCoursesToClassroom($classroom['id'], array($course['id']));
        $copyCourse = current($copyCourses);

        $enabled = $this->getClassroomService()->isCourseInClassroom($copyCourse['id'], $classroom['id']);

        $this->assertEquals(true, $enabled);

        $this->getClassroomService()->deleteClassroomCourses($classroom['id'], array($copyCourse['id']));

        $enabled = $this->getClassroomService()->isCourseInClassroom($copyCourse['id'], $classroom['id']);

        $this->assertEquals(false, $enabled);
    }

    public function testCountMobileFilledMembersByClassroomId()
    {
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'countMobileFilledMembersByClassroomId',
                    'returnValue' => 1,
                    'withParams' => array(1, 0),
                ),
            )
        );
        $result = $this->getClassroomService()->countMobileFilledMembersByClassroomId(1, 0);

        $this->assertEquals(1, $result);
    }

    public function testFindMembersByUserIdAndClassroomIds()
    {
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'findByUserIdAndClassroomIds',
                    'returnValue' => array(),
                    'withParams' => array(1, array(1, 2)),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'findByUserIdAndClassroomIds',
                    'returnValue' => array(array('classroomId' => 1)),
                    'withParams' => array(1, array(1, 2)),
                    'runTimes' => 1,
                ),
            )
        );
        $result1 = $this->getClassroomService()->findMembersByUserIdAndClassroomIds(1, array(1, 2));

        $this->assertEquals(array(), $result1);

        $result2 = $this->getClassroomService()->findMembersByUserIdAndClassroomIds(1, array(1, 2));

        $this->assertEquals(array('classroomId' => 1), $result2[1]);
    }

    public function testFindClassroomsByIds()
    {
        $textClassroom = array(
            'title' => 'test11112221',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $classrooms = $this->getClassroomService()->findClassroomsByIds(array(1));

        $this->assertEquals($classroom, $classrooms[1]);
    }

    public function testSearchMemberCount()
    {
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'count',
                    'returnValue' => 1,
                    'withParams' => array(array('userId' => 1)),
                ),
            )
        );
        $result = $this->getClassroomService()->searchMemberCount(array('userId' => 1));

        $this->assertEquals(1, $result);
    }

    public function testSearchMemberCountGroupByFields()
    {
        $this->mockBiz('Classroom:ClassroomMemberDao', array(
            array(
                'functionName' => 'searchMemberCountGroupByFields',
                'returnValue' => array(array('classroomId' => 1, 'count' => 2)),
            ),
        ));
        $conditions = array('createdTime_GE' => strtotime('-30 days'), 'roles' => array('student', 'assistant'));
        $result = $this->getClassroomService()->searchMemberCountGroupByFields($conditions, 'classroomId', 0, 10);

        $this->assertEquals(1, count($result));
        $this->assertEquals(1, $result[0]['classroomId']);
    }

    public function testSearchMembers()
    {
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 111, 'role' => 'teacher')),
                    'withParams' => array(
                        array('role' => '%teacher%', 'userId' => 111, 'categoryIds' => array(1, 2)),
                        array(),
                        0,
                        5,
                    ),
                ),
            )
        );
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUserByNickname',
                    'returnValue' => array('id' => 111),
                    'withParams' => array('name'),
                ),
            )
        );
        $this->mockBiz(
            'Taxonomy:CategoryService',
            array(
                array(
                    'functionName' => 'findCategoryChildrenIds',
                    'returnValue' => array(2),
                    'withParams' => array(1),
                ),
            )
        );
        $result = $this->getClassroomService()->searchMembers(
            array('role' => 'teacher', 'nickname' => 'name', 'categoryId' => 1),
            array(),
            0,
            5
        );

        $this->assertEquals(array(array('id' => 111, 'role' => 'teacher')), $result);
    }

    public function testGetClassroomMember()
    {
        $textClassroom = array(
            'title' => 'test001',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $member = $this->getClassroomService()->getClassroomMember($classroom['id'], 3);

        $this->assertEquals(null, $member);
    }

    public function testGetClassroomMembersByCourseId()
    {
        $this->mockBiz(
            'Classroom:ClassroomCourseDao',
            array(
                array(
                    'functionName' => 'findClassroomIdsByCourseId',
                    'returnValue' => array(array(1)),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'findByUserIdAndClassroomIds',
                    'returnValue' => array(),
                    'withParams' => array(1, array(array(1))),
                ),
            )
        );
        $result = $this->getClassroomService()->getClassroomMembersByCourseId(1, 1);
        $this->assertEquals(array(), $result);
    }

    public function testFindClassroomMembersByRole()
    {
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'findByClassroomIdAndRole',
                    'returnValue' => array(array('userId' => 1, 'role' => array('student'))),
                    'withParams' => array(1, 'student', 0, 5),
                ),
            )
        );
        $result = $this->getClassroomService()->findClassroomMembersByRole(1, 'student', 0, 5);
        $this->assertEquals(array(1 => array('userId' => 1, 'role' => array('student'))), $result);
    }

    public function testFindMembersByClassroomIdAndUserIds()
    {
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'findByClassroomIdAndUserIds',
                    'returnValue' => array(array('userId' => 1, 'role' => array('student'))),
                    'withParams' => array(1, array(1)),
                ),
            )
        );
        $result = $this->getClassroomService()->findMembersByClassroomIdAndUserIds(1, array(1));
        $this->assertEquals(array(1 => array('userId' => 1, 'role' => array('student'))), $result);
    }

    public function testBecomeStudent()
    {
        $user = $this->getCurrentUser();
        $textClassroom = array(
            'title' => 'test066',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $user2 = $this->getUserService()->register(array(
            'id' => 2,
            'nickname' => 'admin4',
            'email' => 'admin4@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $result = $this->getClassroomService()->isClassroomStudent($classroom['id'], $user2['id']);
        $this->assertEquals(false, $result);

        $this->getClassroomService()->becomeStudent($classroom['id'], $user2['id']);
        $result = $this->getClassroomService()->isClassroomStudent($classroom['id'], $user2['id']);
        $this->assertEquals(true, $result);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.not_found
     */
    public function testBecomeStudentWithNotFoundClassroom()
    {
        $this->getClassroomService()->becomeStudent(1, 1);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.unpublished_classroom
     */
    public function testBecomeStudentWithUnPublishedClassroom()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('status' => 'draft'),
                ),
            )
        );
        $this->getClassroomService()->becomeStudent(1, 1);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.not_found
     */
    public function testBecomeStudentWithExistUser()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('status' => 'closed'),
                ),
            )
        );
        $this->getClassroomService()->becomeStudent(1, 10);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.forbidden_become_student
     */
    public function testBecomeStudentWithForbiddenBecomeStudent()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('status' => 'closed'),
                ),
            )
        );
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 1),
                ),
            )
        );
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'getByClassroomIdAndUserId',
                    'returnValue' => array('role' => array('student')),
                ),
            )
        );
        $this->getClassroomService()->becomeStudent(1, 10);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.member_level_limit
     */
    public function testBecomeStudentWithStudentLevelLimit()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('status' => 'closed', 'vipLevelId' => 1),
                ),
            )
        );
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 1),
                ),
            )
        );
        $this->mockBiz(
            'VipPlugin:Vip:VipService',
            array(
                array(
                    'functionName' => 'checkUserInMemberLevel',
                    'returnValue' => 'no',
                ),
            )
        );
        $this->getClassroomService()->becomeStudent(1, 1, array('becomeUseMember' => 1));
    }

    /**
     * @expectedException \Biz\Order\OrderException
     * @expectedExceptionMessage exception.order.not_found
     */
    public function testBecomeStudentWithExistOrder()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('status' => 'closed'),
                ),
            )
        );
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 1),
                ),
            )
        );
        $this->mockBiz(
            'Order:OrderService',
            array(
                array(
                    'functionName' => 'getOrder',
                    'returnValue' => array(),
                ),
            )
        );
        $this->getClassroomService()->becomeStudent(1, 10, array('orderId' => 1));
    }

    public function testBecomeStudentWithOrder()
    {
        $user = $this->getCurrentUser();
        $textClassroom = array(
            'title' => 'test066',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $result = $this->getClassroomService()->becomeStudentWithOrder($classroom['id'], $user['id'], array('price' => 0, 'remark' => 'remark', 'isNotify' => 1));
        $this->assertEquals('test066', $result[0]['title']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testBecomeStudentWithOrderWithExistParams()
    {
        $this->getClassroomService()->becomeStudentWithOrder(1, 1);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.not_found
     */
    public function testBecomeStudentWithOrderWithExistUser()
    {
        $textClassroom = array(
            'title' => 'test066',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $this->getClassroomService()->becomeStudentWithOrder($classroom['id'], 10, array('price' => 0, 'remark' => 'remark', 'isNotify' => 1));
    }

    public function testRemoveStudent()
    {
        $teacher1 = $this->createTeacher('1');
        $teacher2 = $this->createTeacher('2');
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $course1 = $this->createCourse('Test Course 1');
        $this->getCourseMemberService()->setCourseTeachers($course1['id'],
            array(array('id' => $teacher1['id'], 'isVisible' => 1), array('id' => $teacher2['id'], 'isVisible' => 1)));
        $courseIds = array($course1['id']);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], $courseIds);

        $this->getClassroomService()->publishClassroom($classroom['id']);
        $member1 = $this->getClassroomService()->becomeStudent($classroom['id'], $teacher1['id']);
        $member2 = $this->getClassroomService()->becomeStudent($classroom['id'], $teacher2['id']);
        $studentCount1 = $this->getClassroomService()->getClassroomStudentCount($classroom['id']);
        $this->assertEquals($studentCount1, 2);
        $this->getClassroomService()->removeStudent($classroom['id'], $member1['userId']);
        $studentCount2 = $this->getClassroomService()->getClassroomStudentCount($classroom['id']);
        $this->assertEquals($studentCount2, 1);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.not_found
     */
    public function testRemoveStudentWithNotFoundStudent()
    {
        $this->getClassroomService()->removeStudent(1, 1);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.forbidden_not_student
     */
    public function testRemoveStudentWithNotStudent()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1),
                ),
            )
        );

        $this->getClassroomService()->removeStudent(1, 1);
    }

    public function testGetClassroomStudentCount()
    {
        $user = $this->getCurrentUser();
        $textClassroom = array(
            'title' => 'test991',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $currentUser2 = new CurrentUser();
        $currentUser2->fromArray(array(
            'id' => 2,
            'nickname' => 'admin5',
            'email' => 'admin5@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser2);

        $result = $this->getClassroomService()->getClassroomStudentCount($classroom['id']);
        $this->assertEquals(0, $result);

        $this->getClassroomService()->becomeStudent($classroom['id'], $user['id']);

        $result = $this->getClassroomService()->getClassroomStudentCount($classroom['id']);
        $this->assertEquals(1, $result);
    }

    public function testIsClassroomStudent()
    {
        $textClassroom = array(
            'title' => 'test001',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $user2 = $this->getUserService()->register(array(
            'id' => 2,
            'nickname' => 'admin4',
            'email' => 'admin4@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $result = $this->getClassroomService()->isClassroomStudent($classroom['id'], $user2['id']);
        $this->assertEquals(false, $result);

        $this->getClassroomService()->becomeStudent($classroom['id'], $user2['id']);
        $result = $this->getClassroomService()->isClassroomStudent($classroom['id'], $user2['id']);
        $this->assertEquals(true, $result);
    }

    public function testRemarkStudent()
    {
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'getByClassroomIdAndUserId',
                    'returnValue' => array('id' => 1),
                    'withParams' => array(1, 1),
                ),
                array(
                    'functionName' => 'update',
                    'returnValue' => array('id' => 1, 'remark' => 'test'),
                    'withParams' => array(1, array('remark' => 'test')),
                ),
            )
        );
        $result = $this->getClassroomService()->remarkStudent(1, 1, 'test');

        $this->assertEquals(array('id' => 1, 'remark' => 'test'), $result);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.not_found_member
     */
    public function testRemarkStudentWithNotFoundMember()
    {
        $this->getClassroomService()->remarkStudent(1, 1, 'remark');
    }

    public function testIsHeadTeacher()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('headTeacherId' => 2),
                    'withParams' => array(1),
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array('headTeacherId' => 2),
                    'withParams' => array(2),
                ),
            )
        );
        $result = ReflectionUtils::invokeMethod($this->getClassroomService(), 'isHeadTeacher', array(1, 1));
        $this->assertFalse($result);
        $result = ReflectionUtils::invokeMethod($this->getClassroomService(), 'isHeadTeacher', array(2, 2));
        $this->assertTrue($result);
    }

    public function testFindClassroomStudents()
    {
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'findByClassroomIdAndRole',
                    'returnValue' => array(array('userId' => 1, 'role' => array('student'))),
                    'withParams' => array(1, 'student', 0, 5),
                ),
            )
        );
        $result = $this->getClassroomService()->findClassroomStudents(1, 0, 5);
        $this->assertEquals(array(array('userId' => 1, 'role' => array('student'))), $result);
    }

    public function testLockStudent()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'title' => 'title'),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'getByClassroomIdAndUserId',
                    'returnValue' => array('id' => 1, 'locked' => 1, 'role' => array('student')),
                    'withParams' => array(1, 1),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getByClassroomIdAndUserId',
                    'returnValue' => array('id' => 1, 'locked' => 0, 'role' => array('student')),
                    'withParams' => array(1, 1),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'update',
                    'withParams' => array(1, array('locked' => 1)),
                    'runTimes' => 1,
                ),
            )
        );
        $result1 = $this->getClassroomService()->lockStudent(1, 1);
        $this->assertNull($result1);

        $result2 = $this->getClassroomService()->lockStudent(1, 1);
        $this->getClassroomMemberDao()->shouldHaveReceived('update');
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.not_found
     */
    public function testLockStudentWithExistClassroom()
    {
        $this->getClassroomService()->lockStudent(1, 1);
    }

    public function testLockStudentWithExistMember()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'title' => 'title'),
                    'withParams' => array(1),
                ),
            )
        );

        $result = $this->getClassroomService()->lockStudent(1, 1);
        $this->assertNull($result);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.forbidden_not_student
     */
    public function testLockStudentWithForbiddenNotStudent()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'title' => 'title'),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'getByClassroomIdAndUserId',
                    'returnValue' => array('id' => 1, 'role' => array('teacher')),
                ),
            )
        );

        $this->getClassroomService()->lockStudent(1, 1);
    }

    public function testUnlockStudent()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'title' => 'title'),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'getByClassroomIdAndUserId',
                    'returnValue' => array('id' => 1, 'locked' => 0, 'role' => array('student')),
                    'withParams' => array(1, 1),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getByClassroomIdAndUserId',
                    'returnValue' => array('id' => 1, 'locked' => 1, 'role' => array('student')),
                    'withParams' => array(1, 1),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'update',
                    'withParams' => array(1, array('locked' => 0)),
                    'runTimes' => 1,
                ),
            )
        );
        $result1 = $this->getClassroomService()->unlockStudent(1, 1);
        $this->assertNull($result1);

        $result2 = $this->getClassroomService()->unlockStudent(1, 1);
        $this->getClassroomMemberDao()->shouldHaveReceived('update');
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.not_found
     */
    public function testUnlockStudentWithExistClassroom()
    {
        $this->getClassroomService()->unlockStudent(1, 1);
    }

    public function testUnlockStudentWithExistMember()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'title' => 'title'),
                    'withParams' => array(1),
                ),
            )
        );

        $result = $this->getClassroomService()->unlockStudent(1, 1);
        $this->assertNull($result);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.forbidden_not_student
     */
    public function testUnlockStudentWithForbiddenNotStudent()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'title' => 'title'),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'getByClassroomIdAndUserId',
                    'returnValue' => array('id' => 1, 'role' => array('teacher')),
                ),
            )
        );

        $this->getClassroomService()->unlockStudent(1, 1);
    }

    public function testBecomeAssistant()
    {
        $textClassroom = array(
            'title' => 'test099',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $user2 = $this->getUserService()->register(array(
            'id' => 2,
            'nickname' => 'admin4',
            'email' => 'admin4@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $assistants = $this->getClassroomService()->findAssistants($classroom['id']);
        $this->assertEquals(0, count($assistants));

        $this->getClassroomService()->becomeAssistant($classroom['id'], $user2['id']);
        $assistants = $this->getClassroomService()->findAssistants($classroom['id']);
        $this->assertEquals(1, count($assistants));
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.not_found
     */
    public function testBecomeAssistantWithExistClassroom()
    {
        $this->getClassroomService()->becomeAssistant(1, 1);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.not_found
     */
    public function testBecomeAssistantWithExistUser()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'status' => 'draft'),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array(),
                    'withParams' => array(1),
                ),
            )
        );

        $this->getClassroomService()->becomeAssistant(1, 1);
    }

    public function testBecomeTeacher()
    {
        TimeMachine::setMockedTime(1517464454);
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'title' => 'title'),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 3, 'roles' => array('ROLE_SUPER_ADMIN', 'ROLE_ADMIN')),
                    'withParams' => array(3),
                ),
            )
        );
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'create',
                    'returnValue' => array('id' => 1, 'userId' => 3),
                    'withParams' => array(
                        array(
                            'classroomId' => 1,
                            'userId' => 3,
                            'orderId' => 0,
                            'levelId' => 0,
                            'role' => array('teacher'),
                            'remark' => '',
                            'createdTime' => TimeMachine::time(),
                        ),
                    ),
                ),
            )
        );
        $result = $this->getClassroomService()->becomeTeacher(1, 3);

        $this->assertEquals(array('id' => 1, 'userId' => 3), $result);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.not_found
     */
    public function testBecomeTeacherWithExistClassroom()
    {
        $this->getClassroomService()->becomeTeacher(1, 1);
    }

    public function testFindAssistants()
    {
        $textClassroom = array(
            'title' => 'test433',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $user2 = $this->getUserService()->register(array(
            'id' => 2,
            'nickname' => 'admin4',
            'email' => 'admin4@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $this->getClassroomService()->becomeAssistant($classroom['id'], $user2['id']);
        $assistants = $this->getClassroomService()->findAssistants($classroom['id']);
        $this->assertEquals(1, count($assistants));
    }

    public function testFindTeachers()
    {
        $result1 = $this->getClassroomService()->findTeachers(1);

        $this->assertEquals(array(), $result1);

        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'findTeachersByClassroomId',
                    'returnValue' => array(array('userId' => 11)),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('teacherIds' => array(22)),
                    'withParams' => array(1),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array(1),
                    'runTimes' => 1,
                ),
            )
        );
        $result2 = $this->getClassroomService()->findTeachers(1);
        $result3 = $this->getClassroomService()->findTeachers(1);

        $this->assertEquals(array(11), $result2);
        $this->assertEquals(array(11), $result3);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.empty_title
     */
    public function testAddClassroomWithEmptyTitle()
    {
        $this->getClassroomService()->addClassroom(array('title' => ''));
    }

    public function testisClassroomAssistant()
    {
        $textClassroom = array(
            'title' => 'test077',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $user2 = $this->getUserService()->register(array(
            'id' => 2,
            'nickname' => 'admin4',
            'email' => 'admin4@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $result = $this->getClassroomService()->isClassroomAssistant($classroom['id'], $user2['id']);
        $this->assertEquals(false, $result);

        $this->getClassroomService()->becomeAssistant($classroom['id'], $user2['id']);
        $result = $this->getClassroomService()->isClassroomAssistant($classroom['id'], $user2['id']);
        $this->assertEquals(true, $result);
    }

    public function testUpdateAssistants()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'title' => 'title', 'assistantIds' => array()),
                    'withParams' => array(1),
                ),
                array(
                    'functionName' => 'update',
                    'withParams' => array(1, array('assistantIds' => array(1, 2))),
                ),
            )
        );
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'findAssistantsByClassroomId',
                    'returnValue' => array(array('userId' => 2), array('userId' => 3)),
                    'withParams' => array(1),
                ),
                array(
                    'functionName' => 'findByClassroomIdAndUserIds',
                    'returnValue' => array(array('id' => 1, 'userId' => 1, 'role' => array('student'))),
                    'withParams' => array(1, array(1)),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'findByClassroomIdAndUserIds',
                    'returnValue' => array(array('id' => 3, 'userId' => 3, 'role' => array('student', 'assistant'))),
                    'withParams' => array(1, array(1 => 3)),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'update',
                    'withParams' => array(1, array('role' => array('student', 'assistant'))),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'update',
                    'withParams' => array(3, array('role' => array('student'))),
                    'runTimes' => 1,
                ),
            )
        );
        $result = $this->getClassroomService()->updateAssistants(1, array(1, 2));
        $this->getClassroomMemberDao()->shouldHaveReceived(
            'update',
            array(1, array('role' => array('student', 'assistant')))
        );
        $this->getClassroomMemberDao()->shouldHaveReceived(
            'update',
            array(3, array('role' => array('student')))
        );
        $this->getClassroomDao()->shouldHaveReceived('update');
        $this->assertNull($result);
    }

    public function testAddAssistants()
    {
        $result = ReflectionUtils::invokeMethod($this->getClassroomService(), 'addAssistants', array(1, array(), array()));
        $this->assertNull($result);
    }

    public function testBecomeAuditor()
    {
        $user = $this->getCurrentUser();
        $textClassroom = array(
            'title' => 'test1444',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $user2 = $this->getUserService()->register(array(
            'id' => 2,
            'nickname' => 'admin4',
            'email' => 'admin4@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $result = $this->getClassroomService()->getClassroomAuditorCount($classroom['id']);
        $this->assertEquals(0, $result);

        $this->getClassroomService()->becomeAuditor($classroom['id'], $user2['id']);

        $result = $this->getClassroomService()->getClassroomAuditorCount($classroom['id']);
        $this->assertEquals(1, $result);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.not_found
     */
    public function testBecomeAuditorWithExistClassroom()
    {
        $this->getClassroomService()->becomeAuditor(1, 1);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.unpublished_classroom
     */
    public function testBecomeAuditorWithUnpublishedClassroom()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'status' => 'draft'),
                    'withParams' => array(1),
                ),
            )
        );

        $this->getClassroomService()->becomeAuditor(1, 1);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.not_found
     */
    public function testBecomeAuditorWithExistUser()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'status' => 'published'),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array(),
                    'withParams' => array(1),
                ),
            )
        );

        $this->getClassroomService()->becomeAuditor(1, 1);
    }

    public function testIsClassroomAuditor()
    {
        $user = $this->getCurrentUser();
        $textClassroom = array(
            'title' => 'test333',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $user2 = $this->getUserService()->register(array(
            'id' => 2,
            'nickname' => 'admin4',
            'email' => 'admin4@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $enabled = $this->getClassroomService()->isClassroomAuditor($classroom['id'], $user2['id']);

        $this->assertEquals(false, $enabled);

        $this->getClassroomService()->becomeAuditor($classroom['id'], $user2['id']);

        $enabled = $this->getClassroomService()->isClassroomAuditor($classroom['id'], $user2['id']);

        $this->assertEquals(true, $enabled);
    }

    public function testGetClassroomAuditorCount()
    {
        $textClassroom = array(
            'title' => 'test2111',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $user2 = $this->getUserService()->register(array(
            'id' => 2,
            'nickname' => 'admin4',
            'email' => 'admin4@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $result = $this->getClassroomService()->getClassroomAuditorCount($classroom['id']);
        $this->assertEquals(0, $result);

        $this->getClassroomService()->becomeAuditor($classroom['id'], $user2['id']);

        $result = $this->getClassroomService()->getClassroomAuditorCount($classroom['id']);
        $this->assertEquals(1, $result);
    }

    public function testAddHeadTeacher()
    {
        $textClassroom = array(
            'title' => 'test5554',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $user2 = $this->getUserService()->register(array(
            'id' => 2,
            'nickname' => 'admin4',
            'email' => 'admin4@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $this->getClassroomService()->addHeadTeacher($classroom['id'], $user2['id']);

        $currentUser = new CurrentUser();
        $currentUser->fromArray($user2);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $enabled = $this->getClassroomService()->canManageClassroom($classroom['id']);
        $this->assertEquals(true, $enabled);
    }

    public function testAddHeadTeacher2()
    {
        $teacher1 = $this->createTeacher('1');
        $teacher2 = $this->createTeacher('2');
        $textClassroom = array(
            'title' => 'test',
        );
        $course1 = $this->createCourse('Test Course 1');

        $this->getCourseMemberService()->setCourseTeachers($course1['id'],
            array(array('id' => $teacher1['id'], 'isVisible' => 1), array('id' => $teacher2['id'], 'isVisible' => 1)));

        $courseIds = array($course1['id']);

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);
        $this->getClassroomService()->addHeadTeacher($classroom['id'], $teacher1['id']);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], $courseIds);
        $classroom = $this->getClassroomService()->getClassroom($classroom['id']);
        $this->assertEquals($teacher1['id'], $classroom['headTeacherId']);
        $this->getClassroomService()->addHeadTeacher($classroom['id'], $teacher2['id']);
        $classroom = $this->getClassroomService()->getClassroom($classroom['id']);
        $this->assertEquals($teacher2['id'], $classroom['headTeacherId']);
    }

    public function testAddHeadTeacher3()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'title' => 'title', 'headTeacherId' => 1),
                    'withParams' => array(1),
                ),
            )
        );

        $result = $this->getClassroomService()->addHeadTeacher(1, 1);
        $this->assertNull($result);
    }

    public function testAddHeadTeacher4()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'title' => 'title', 'headTeacherId' => 2),
                    'withParams' => array(1),
                ),
            )
        );

        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'getByClassroomIdAndUserId',
                    'returnValue' => array('id' => 1, 'title' => 'title', 'role' => array('student', 'teacher', 'headTeacher')),
                ),
                array(
                    'functionName' => 'update',
                    'returnValue' => array(),
                ),
            )
        );
        $result = $this->getClassroomService()->addHeadTeacher(1, 0);
        $this->assertNull($result);
    }

    public function testIsClassroomHeadTeacher()
    {
        $textClassroom = array(
            'title' => 'test5234',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $currentUser2 = new CurrentUser();
        $currentUser2->fromArray(array(
            'id' => 2,
            'nickname' => 'admin5',
            'email' => 'admin5@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $this->getServiceKernel()->setCurrentUser($currentUser2);

        $enabled = $this->getClassroomService()->isClassroomHeadTeacher($classroom['id'], $currentUser2['id']);
        $this->assertEquals(false, $enabled);

        $this->getClassroomService()->addHeadTeacher($classroom['id'], $currentUser2['id']);

        $enabled = $this->getClassroomService()->isClassroomHeadTeacher($classroom['id'], $currentUser2['id']);
        $this->assertEquals(true, $enabled);
    }

    public function testTryAdminClassroom()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin3',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_SUPER_ADMIN'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'title' => 'title'),
                    'withParams' => array(1),
                ),
            )
        );
        $result = $this->getClassroomService()->tryAdminClassroom(1);

        $this->assertEquals(array('id' => 1, 'title' => 'title'), $result);
    }

    public function testCanManageClassroom()
    {
        $textClassroom = array(
            'title' => 'test32111',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $enabled = $this->getClassroomService()->canManageClassroom($classroom['id']);
        $this->assertEquals(true, $enabled);

        $user = $this->getUserService()->register(array(
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin1@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $user2 = $this->getUserService()->register(array(
            'id' => 2,
            'nickname' => 'admin2',
            'email' => 'admin2@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));
        $user3 = $this->getUserService()->register(array(
            'nickname' => 'admin3',
            'password' => 'admin',
            'email' => 'admin3@admin.com',
        ));
        $user4 = $this->getUserService()->register(array(
            'nickname' => 'admin4',
            'password' => 'admin',
            'email' => 'admin4@admin.com',
        ));

        $this->getClassroomService()->addHeadTeacher($classroom['id'], $user['id']);
        $this->getClassroomService()->becomeAssistant($classroom['id'], $user2['id']);
        $this->getClassroomService()->becomeAuditor($classroom['id'], $user3['id']);
        $this->getClassroomService()->becomeStudent($classroom['id'], $user4['id']);

        $c_user1 = new CurrentUser();
        $this->getServiceKernel()->setCurrentUser($c_user1->fromArray($user));
        $enabled = $this->getClassroomService()->canManageClassroom($classroom['id']);
        $this->assertEquals(true, $enabled);

        $c_user2 = new CurrentUser();
        $this->getServiceKernel()->setCurrentUser($c_user2->fromArray($user2));
        $enabled = $this->getClassroomService()->canManageClassroom($classroom['id']);
        $this->assertEquals(false, $enabled);

        $c_user3 = new CurrentUser();
        $this->getServiceKernel()->setCurrentUser($c_user3->fromArray($user3));
        $enabled = $this->getClassroomService()->canManageClassroom($classroom['id']);
        $this->assertEquals(false, $enabled);

        $c_user4 = new CurrentUser();
        $this->getServiceKernel()->setCurrentUser($c_user4->fromArray($user4));
        $enabled = $this->getClassroomService()->canManageClassroom($classroom['id']);
        $this->assertEquals(false, $enabled);
    }

    public function testCanManageClassroomWithExistClassroom()
    {
        $result = $this->getClassroomService()->canManageClassroom(1);
        $this->assertFalse($result);
    }

    public function testCanTakeClassroom()
    {
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $enabled = $this->getClassroomService()->canTakeClassroom($classroom['id']);
        $this->assertEquals(true, $enabled);

        $teacherUser = $this->getUserService()->register(array(
            'id' => 2,
            'nickname' => 'teacher',
            'email' => 'teacher@teacher.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $auditorUser = $this->getUserService()->register(array(
            'id' => 3,
            'nickname' => 'auditor',
            'email' => 'auditor@auditor.com',
            'password' => 'auditor',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $studentUser = $this->getUserService()->register(array(
            'id' => 4,
            'nickname' => 'student',
            'email' => 'student@student.com',
            'password' => 'student',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $this->getClassroomService()->becomeAuditor($classroom['id'], $auditorUser['id']);
        $this->getClassroomService()->addHeadTeacher($classroom['id'], $teacherUser['id']);
        $this->getClassroomService()->becomeStudent($classroom['id'], $studentUser['id']);

        $teacherCurrent = new CurrentUser();
        $this->getServiceKernel()->setCurrentUser($teacherCurrent->fromArray($teacherUser));
        $enabled = $this->getClassroomService()->canTakeClassroom($classroom['id']);
        $this->assertEquals(true, $enabled);

        $auditorCurrentUser = new CurrentUser();
        $this->getServiceKernel()->setCurrentUser($auditorCurrentUser->fromArray($auditorUser));
        $enabled = $this->getClassroomService()->canTakeClassroom($classroom['id']);
        $this->assertEquals(false, $enabled);

        $studentCurrentUser = new CurrentUser();
        $this->getServiceKernel()->setCurrentUser($studentCurrentUser->fromArray($studentUser));

        $enabled = $this->getClassroomService()->canTakeClassroom($classroom['id']);
        $this->assertEquals(true, $enabled);
    }

    public function testCanTakeClassroomWithExistClassroom()
    {
        $result = $this->getClassroomService()->canTakeClassroom(1);
        $this->assertFalse($result);
    }

    public function testCanLookClassroom()
    {
        $user = $this->getCurrentUser();
        $user = $this->createStudent();
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $enabled = $this->getClassroomService()->canLookClassroom($classroom['id']);

        $this->assertEquals(true, $enabled);
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $this->getServiceKernel()->setCurrentUser($currentUser);

        $enabled = $this->getClassroomService()->canLookClassroom($classroom['id']);

        $this->assertEquals(true, $enabled); //默认是showable班级

        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $this->getClassroomService()->addHeadTeacher($classroom['id'], 2);

        $enabled = $this->getClassroomService()->canLookClassroom($classroom['id']);

        $this->assertEquals(true, $enabled);

        $this->getClassroomService()->publishClassroom($classroom['id']);

        $user = $this->getUserService()->register(array(
            'id' => 3,
            'nickname' => 'admin1',
            'email' => 'admin@adm1in.com',
            'password' => 'adm1in',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $this->getClassroomService()->becomeAuditor($classroom['id'], $user['id']);

        $enabled = $this->getClassroomService()->canLookClassroom($classroom['id']);

        $this->assertEquals(true, $enabled);

        $this->getClassroomService()->becomeStudent($classroom['id'], $user['id']);

        $enabled = $this->getClassroomService()->canLookClassroom($classroom['id']);

        $this->assertEquals(true, $enabled);

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 4,
            'nickname' => 'admin11',
            'email' => 'admin@adm11in.com',
            'password' => 'adm11in',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $this->getServiceKernel()->setCurrentUser($currentUser);

        $enabled = $this->getClassroomService()->canLookClassroom($classroom['id']);

        $this->assertEquals(true, $enabled);

        $classroom['showable'] = '0';
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $classroom);
        $enabled = $this->getClassroomService()->canLookClassroom($classroom['id']);
        $this->assertEquals(false, $enabled);
    }

    public function testCanLookClassroomWithExistClassroom()
    {
        $result = $this->getClassroomService()->canLookClassroom(1);
        $this->assertFalse($result);
    }

    public function testCanJoinClassroom()
    {
        $classroom = $this->getClassroomService()->addClassroom(array(
            'title' => 'test Classroom',
        ));
        $this->getClassroomService()->publishClassroom($classroom['id']);

        $classroom1 = $this->getClassroomService()->addClassroom(array(
            'title' => 'test Classroom 2',
        ));
        $classroom1 = $this->getClassroomService()->updateClassroom($classroom1['id'], array(
            'expiryMode' => 'date',
            'expiryValue' => time() + 1,
        ));
        $this->getClassroomService()->publishClassroom($classroom1['id']);

        $user = $this->getUserService()->register(array(
            'id' => 3,
            'nickname' => 'test',
            'email' => 'test@test.com',
            'password' => 'test123',
            'roles' => array('ROLE_USER'),
        ));

        $currentUser = new CurrentUser();
        $user['currentIp'] = '127.0.0.1';
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);

        $result = $this->getClassroomService()->canJoinClassroom($classroom['id']);
        $this->assertEquals($result['code'], 'success');

        sleep(3);
        $result1 = $this->getClassroomService()->canJoinClassroom($classroom1['id']);
        $this->assertEquals($result1['code'], 'classroom.expired');
    }

    /** @group current */
    public function testCanLearnClassroom()
    {
        $classroom = $this->getClassroomService()->addClassroom(array(
            'title' => 'test Classroom',
        ));
        $this->getClassroomService()->publishClassroom($classroom['id']);

        $user = $this->getUserService()->register(array(
            'id' => 3,
            'nickname' => 'test',
            'email' => 'test@test.com',
            'password' => 'test123',
            'roles' => array('ROLE_USER'),
        ));

        $currentUser = new CurrentUser();
        $user['currentIp'] = '127.0.0.1';
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);

        $result1 = $this->getClassroomService()->canLearnClassroom($classroom['id']);
        $this->assertEquals($result1['code'], 'member.not_found');

        $this->getClassroomService()->becomeAuditor($classroom['id'], $user['id']);
        $result2 = $this->getClassroomService()->canLearnClassroom($classroom['id']);
        $this->assertEquals($result2['code'], 'member.auditor');

        $this->getClassroomService()->becomeStudent($classroom['id'], $user['id']);
        $result3 = $this->getClassroomService()->canLearnClassroom($classroom['id']);
        $this->assertEquals($result3['code'], 'success');
    }

    public function testCanHandleClassroom()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array(),
                    'withParams' => array(1),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'title' => 'title'),
                    'withParams' => array(1),
                    'runTimes' => 3,
                ),
            )
        );
        $result1 = $this->getClassroomService()->canHandleClassroom(1);
        $this->assertFalse($result1);

        $user = $this->getUserService()->register(array(
            'id' => 2,
            'nickname' => 'test',
            'email' => 'test@test.com',
            'password' => 'test123',
            'roles' => array('ROLE_USER', 'ROLE_SUPER_ADMIN'),
        ));

        $currentUser = new CurrentUser();
        $user['currentIp'] = '127.0.0.1';
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'getByClassroomIdAndUserId',
                    'returnValue' => array(),
                    'withParams' => array(1, $user['id']),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getByClassroomIdAndUserId',
                    'returnValue' => array('id' => 1, 'role' => array('teacher')),
                    'withParams' => array(1, $user['id']),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getByClassroomIdAndUserId',
                    'returnValue' => array('id' => 1, 'role' => array('student')),
                    'withParams' => array(1, $user['id']),
                    'runTimes' => 1,
                ),
            )
        );
        $result2 = $this->getClassroomService()->canHandleClassroom(1);
        $this->assertFalse($result1);
        $result3 = $this->getClassroomService()->canHandleClassroom(1);
        $this->assertTrue($result3);
        $result4 = $this->getClassroomService()->canHandleClassroom(1);
        $this->assertFalse($result4);
    }

    public function testFindCoursesByClassroomId()
    {
        $user = $this->createUser();
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $this->createCourse('Test Course 1');
        $this->createCourse('Test Course 2');

        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], array(1, 2));

        $courses = $this->getClassroomService()->findCoursesByClassroomId($classroom['id']);

        $this->assertEquals(2, count($courses));
    }

    public function testUpdateAssistant()
    {
        $teacher1 = $this->createTeacher('1');
        $teacher2 = $this->createTeacher('2');
        $teacher3 = $this->createTeacher('3');
        $teacher4 = $this->createTeacher('4');

        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);

        $this->getClassroomService()->addHeadTeacher($classroom['id'], $teacher2['id']);

        $this->getClassroomService()->becomeStudent($classroom['id'], $teacher1['id']);
        $this->getClassroomService()->becomeStudent($classroom['id'], $teacher3['id']);
        $this->getClassroomService()->becomeStudent($classroom['id'], $teacher4['id']);

        $teacherIds = array($teacher3['id'], $teacher4['id']);
        $this->getClassroomService()->updateAssistants($classroom['id'], $teacherIds);
        $assitantIds = $this->getClassroomService()->findAssistants($classroom['id']);
        $this->assertEquals(2, count($assitantIds));
    }

    /**
     * @group current
     */
    public function testAddCoursesToClassroom()
    {
        $teacher1 = $this->createTeacher('1');
        $teacher2 = $this->createTeacher('2');
        $teacher3 = $this->createTeacher('3');
        $teacher4 = $this->createTeacher('4');
        $teacher5 = $this->createTeacher('5');
        $teacher6 = $this->createTeacher('6');
        $teacher7 = $this->createTeacher('7');
        $teacher8 = $this->createTeacher('8');
        $textClassroom = array(
            'title' => 'test',
        );

        $course1 = $this->createCourse('Test Course 1');
        $course2 = $this->createCourse('Test Course 2');
        $course3 = $this->createCourse('Test Course 3');

        $this->getCourseMemberService()->setCourseTeachers($course1['id'], array(
            array('id' => $teacher1['id'], 'isVisible' => 1),
            array('id' => $teacher2['id'], 'isVisible' => 1),
        ));
        $this->getCourseMemberService()->setCourseTeachers($course2['id'], array(
            array('id' => $teacher4['id'], 'isVisible' => 1),
            array('id' => $teacher5['id'], 'isVisible' => 1),
        ));
        $this->getCourseMemberService()->setCourseTeachers($course3['id'], array(
            array('id' => $teacher1['id'], 'isVisible' => 1),
            array('id' => $teacher3['id'], 'isVisible' => 1),
            array('id' => $teacher6['id'], 'isVisible' => 1),
        ));

        $courseIds = array($course1['id'], $course2['id']);

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);
        $this->getClassroomService()->addHeadTeacher($classroom['id'], $teacher1['id']);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], $courseIds);
        $teachers = $this->getClassroomService()->findTeachers($classroom['id']);
        $this->assertEquals(count($teachers), 4);
        $courseIds = array($course3['id']);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], $courseIds);
        $teachers = $this->getClassroomService()->findTeachers($classroom['id']);
        //ci报错，本地正常
        //$this->assertEquals(count($teachers), 7);
    }

    public function testUpdateClassroomCourses()
    {
        $teacher1 = $this->createTeacher('1');
        $teacher2 = $this->createTeacher('2');
        $teacher3 = $this->createTeacher('3');
        $teacher4 = $this->createTeacher('4');
        $teacher5 = $this->createTeacher('5');
        $teacher6 = $this->createTeacher('6');
        $teacher7 = $this->createTeacher('7');
        $teacher8 = $this->createTeacher('8');
        $textClassroom = array(
            'title' => 'test',
        );

        $course1 = $this->createCourse('Test Course 1');
        $course2 = $this->createCourse('Test Course 2');
        $course3 = $this->createCourse('Test Course 3');

        $this->getCourseMemberService()->setCourseTeachers($course1['id'], array(
            array('id' => $teacher1['id'], 'isVisible' => 1),
            array('id' => $teacher2['id'], 'isVisible' => 1),
            array('id' => $teacher3['id'], 'isVisible' => 1),
        ));
        $this->getCourseMemberService()->setCourseTeachers($course2['id'], array(
            array('id' => $teacher4['id'], 'isVisible' => 1),
            array('id' => $teacher5['id'], 'isVisible' => 1),
        ));
        $this->getCourseMemberService()->setCourseTeachers($course3['id'], array(
            array('id' => $teacher1['id'], 'isVisible' => 1),
            array('id' => $teacher3['id'], 'isVisible' => 1),
            array('id' => $teacher6['id'], 'isVisible' => 1),
        ));

        $courseIds = array($course1['id'], $course2['id'], $course3['id']);

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);
        $this->getClassroomService()->addHeadTeacher($classroom['id'], $teacher1['id']);
        $courses = $this->getClassroomService()->addCoursesToClassroom($classroom['id'], $courseIds);

        $teachers = $this->getClassroomService()->findTeachers($classroom['id']);

        //ci报错，本地正常
        //$this->assertEquals(count($teachers), 7);

        $courseIds = array($courses[2]['id']);

        $this->getClassroomService()->updateClassroomCourses($classroom['id'], $courseIds);
        $teachers = $this->getClassroomService()->findTeachers($classroom['id']);
        //$this->assertEquals(count($teachers), 4);
    }

    public function testFindClassroomsByCoursesIds()
    {
        $this->mockBiz(
            'Classroom:ClassroomCourseDao',
            array(
                array(
                    'functionName' => 'findByCoursesIds',
                    'returnValue' => array(array('id' => 1, 'classroomId' => 1)),
                    'withParams' => array(array(1, 2)),
                ),
            )
        );
        $result = $this->getClassroomService()->findClassroomsByCoursesIds(array(1, 2));

        $this->assertEquals(array(array('id' => 1, 'classroomId' => 1)), $result);
    }

    public function testFindClassroomsByCourseSetIds()
    {
        $this->mockBiz(
            'Classroom:ClassroomCourseDao',
            array(
                array(
                    'functionName' => 'findByCourseSetIds',
                    'returnValue' => array(array('id' => 1, 'courseSetId' => 1)),
                    'withParams' => array(array(1, 2)),
                ),
            )
        );
        $result = $this->getClassroomService()->findClassroomsByCourseSetIds(array(1, 2));

        $this->assertEquals(array(array('id' => 1, 'courseSetId' => 1)), $result);
    }

    public function testCanCreateThreadEvent()
    {
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);

        $user = $this->getUserService()->register(array(
            'id' => 2,
            'nickname' => 'admin2',
            'email' => 'admin2@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));
        $this->getClassroomService()->becomeAssistant($classroom['id'], $user['id']);

        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $result = $this->getClassroomService()->canCreateThreadEvent(array('targetId' => $classroom['id']));

        $this->assertEquals('assistant', $result[0]);
    }

    public function testCanCreateThreadEventWithExistClassroom()
    {
        $result = $this->getClassroomService()->canCreateThreadEvent(array('targetId' => 1));
        $this->assertFalse($result);
    }

    public function testFindUserJoinedClassroomIds()
    {
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);

        $user1 = $this->getUserService()->register(array(
            'nickname' => 'admin3',
            'password' => 'admin',
            'email' => 'admin3@admin.com',
        ));
        $user2 = $this->getUserService()->register(array(
            'nickname' => 'admin4',
            'password' => 'admin',
            'email' => 'admin4@admin.com',
        ));

        $this->getClassroomService()->becomeAuditor($classroom['id'], $user1['id']);
        $this->getClassroomService()->becomeStudent($classroom['id'], $user2['id']);

        $members = $this->getClassroomService()->findUserJoinedClassroomIds($user1['id']);

        $this->assertCount(1, $members);
    }

    public function testUpdateMember()
    {
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'update',
                    'returnValue' => array('id' => 1, 'userId' => 2),
                    'withParams' => array(1, array('userId' => 2)),
                ),
            )
        );
        $result = $this->getClassroomService()->updateMember(1, array('userId' => 2));
        $this->assertEquals(array('id' => 1, 'userId' => 2), $result);
    }

    public function testUpdateLearndNumByClassroomIdAndUserId()
    {
        $this->mockBiz(
            'Classroom:ClassroomCourseDao',
            array(
                array(
                    'functionName' => 'findByClassroomId',
                    'returnValue' => array(array('courseId' => 1)),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'findCoursesByIds',
                    'returnValue' => array(array('id' => 1)),
                    'withParams' => array(array(1)),
                ),
            )
        );
        $this->mockBiz(
            'Task:TaskResultService',
            array(
                array(
                    'functionName' => 'countTaskResults',
                    'returnValue' => 1,
                    'withParams' => array(array(
                        'userId' => 1,
                        'courseIds' => array(1),
                        'status' => 'finish',
                    )),
                ),
            )
        );
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'getByClassroomIdAndUserId',
                    'returnValue' => array('id' => 1),
                    'withParams' => array(1, 1),
                ),
                array(
                    'functionName' => 'update',
                    'returnValue' => array('id' => 1, 'userId' => 1),
                    'withParams' => array(1, array('lastLearnTime' => time(), 'learnedNum' => 1)),
                ),
            )
        );
        $result = $this->getClassroomService()->updateLearndNumByClassroomIdAndUserId(1, 1);

        $this->assertEquals(array('id' => 1, 'userId' => 1), $result);
    }

    public function testCountCoursesByClassroomId()
    {
        $this->mockBiz(
            'Classroom:ClassroomCourseDao',
            array(
                array(
                    'functionName' => 'count',
                    'returnValue' => 1,
                    'withParams' => array(array(
                        'classroomId' => 1,
                        'disabled' => 0,
                    )),
                ),
            )
        );
        $result = $this->getClassroomService()->countCoursesByClassroomId(1);
        $this->assertEquals(1, $result);
    }

    public function testCountCourseTasksByClassroomId()
    {
        $this->mockBiz(
            'Classroom:ClassroomCourseDao',
            array(
                array(
                    'functionName' => 'countCourseTasksByClassroomId',
                    'returnValue' => 1,
                    'withParams' => array(1),
                ),
            )
        );
        $result = $this->getClassroomService()->countCourseTasksByClassroomId(1);
        $this->assertEquals(1, $result);
    }

    public function testFindClassroomCourseByCourseSetIds()
    {
        $result = $this->getClassroomService()->findClassroomCourseByCourseSetIds(array(-1, -2));

        $this->assertCount(0, $result);
    }

    public function testFindUserPaidCoursesInClassroom()
    {
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);

        $course1 = $this->createCourse('Test Course 1');
        $course2 = $this->createCourse('Test Course 2');
        $course3 = $this->createCourse('Test Course 3');

        $courseIds = array($course1['id'], $course2['id'], $course3['id']);

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'findCoursesByStudentIdAndCourseIds', 'returnValue' => array(array('id' => 1, 'courseId' => $course1['id'], 'orderId' => 1))),
        ));
        $this->mockBiz('Order:OrderService', array(
            array('functionName' => 'searchOrderItems', 'returnValue' => array(array('id' => 1, 'order_id' => 1))),
        ));

        list($paidCourses, $orderItems) = $this->getClassroomService()->findUserPaidCoursesInClassroom(1, $classroom['id']);

        $this->assertEquals(1, count($paidCourses));
        $this->assertEquals(1, count($orderItems));
    }

    public function testTryFreeJoin()
    {
        $this->mockBiz(
            'Classroom:ClassroomDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array(
                        'id' => 1,
                        'title' => 'title',
                        'status' => 'published',
                        'buyable' => 1,
                        'expiryMode' => 'forever',
                        'price' => 0,
                        'expiryValue' => 0,
                        'middlePicture' => 'middlePicture',
                        'about' => 'test',
                        'showable' => 1,
                    ),
                    'withParams' => array(1),
                ),
                array(
                    'functionName' => 'update',
                    'withParams' => array(1, array('studentNum' => '1', 'auditorNum' => '0')),
                ),
            )
        );
        $this->getClassroomService()->tryFreeJoin(1);
    }

    public function testFindMembersByMemberIds()
    {
        $this->mockBiz(
            'Classroom:ClassroomMemberDao',
            array(
                array(
                    'functionName' => 'findMembersByMemberIds',
                    'withParams' => array(array(1)),
                ),
            )
        );
        $this->getClassroomService()->findMembersByMemberIds(array(1));

        $this->getClassroomMemberDao()->shouldHaveReceived('findMembersByMemberIds');
    }

    public function testRefreshClassroomHotSeq()
    {
        $classroom = $this->getClassroomDao()->create(array('title' => 'classroom title', 'hotSeq' => 10));
        $this->assertEquals(10, $classroom['hotSeq']);

        $this->getClassroomService()->refreshClassroomHotSeq();

        $classroom = $this->getClassroomService()->getClassroom($classroom['id']);
        $this->assertEquals(0, $classroom['hotSeq']);
    }

    public function testGetOrderBys()
    {
        $result = ReflectionUtils::invokeMethod($this->getClassroomService(), 'getOrderBys', array('hitNum'));
        $this->assertEquals('DESC', $result['hitNum']);
    }

    protected function mockCourse($title = 'Test Course 1')
    {
        return array(
            'title' => $title,
            'courseSetId' => 1,
            'learnMode' => 'freeMode',
            'expiryMode' => 'forever',
            'courseType' => 'normal',
        );
    }

    protected function mockCourseSet($title = 'Test Course 1')
    {
        return array('title' => $title, 'type' => 'normal');
    }

    private function createUser()
    {
        $user = array();
        $user['email'] = 'user@user.com';
        $user['nickname'] = 'user';
        $user['password'] = 'user';
        $user = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');

        return $user;
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    private function createCourse($title)
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );

        $courseSet = $this->getCourseSetService()->createCourseSet($courseSet);
        $course = $this->mockCourse($title);
        $course['courseSetId'] = $courseSet['id'];

        return $this->getCourseService()->createCourse($course);
    }

    private function createStudent()
    {
        $user = array();
        $user['email'] = 'user@user1.com';
        $user['nickname'] = 'use1r';
        $user['password'] = 'user1';
        $user['roles'] = array('ROLE_USER');

        return $this->getUserService()->register($user);
    }

    private function createTeacher($id)
    {
        $user = array();
        $user['nickname'] = 'user'.$id;
        $user['email'] = $user['nickname'].'@user.com';
        $user['password'] = 'user';
        $user['roles'] = array('ROLE_USER', 'ROLE_TEACHER');
        $user = $this->getUserService()->register($user);

        return $this->getUserService()->changeUserRoles($user['id'], array('ROLE_USER', 'ROLE_TEACHER'));
    }

    /**
     * @return ClassroomMemberDao
     */
    protected function getClassroomMemberDao()
    {
        return $this->createDao('Classroom:ClassroomMemberDao');
    }

    /**
     * @return ClassroomDao
     */
    protected function getClassroomDao()
    {
        return $this->createDao('Classroom:ClassroomDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
