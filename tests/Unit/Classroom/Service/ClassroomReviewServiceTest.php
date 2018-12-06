<?php

namespace Tests\Unit\Classroom\Service;

use Biz\BaseTestCase;
use Biz\Classroom\Service\ClassroomReviewService;
use Biz\User\CurrentUser;

class ClassroomReviewServiceTest extends BaseTestCase
{
    public function testGetReview()
    {
        $user = $this->getCurrentUser();
        $classroom = array(
            'title' => 'test',
        );
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $fields = array(
            'title' => 'test',
            'content' => 'test_content',
            'userId' => $user['id'],
            'classroomId' => $classroom['id'],
            'rating' => 1,
        );
        $review = $this->getClassRoomReviewService()->saveReview($fields);
        $result = $this->getClassRoomReviewService()->getReview($classroom['id']);
        $this->assertEquals('test', $result['title']);
    }

    public function testSearchReviews()
    {
        $user = $this->getCurrentUser();
        $user1 = $this->createStudentUser();

        $classroom = array(
            'title' => 'test',
        );
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $fields1 = array(
            'title' => 'test1',
            'content' => 'test_content',
            'userId' => $user1['id'],
            'classroomId' => $classroom['id'],
            'rating' => 1,
        );
        $this->getClassRoomReviewService()->saveReview($fields1);

        $fields2 = array(
            'title' => 'test2',
            'content' => 'test_content',
            'userId' => $user['id'],
            'classroomId' => $classroom['id'],
            'rating' => 2,
        );
        $this->getClassRoomReviewService()->saveReview($fields2);

        $results = $this->getClassRoomReviewService()->searchReviews(array('classroomId' => $classroom['id']), array('rating' => 'DESC'), 0, 5);
        $this->assertCount(2, $results);

        $results = $this->getClassRoomReviewService()->searchReviews(array('author' => $user1['nickname']), array('rating' => 'DESC'), 0, 5);
        $this->assertCount(1, $results);
    }

    public function testSearchReviewCount()
    {
        $user = $this->getCurrentUser();
        $user1 = $this->createStudentUser();

        $classroom = array(
            'title' => 'test',
        );
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $fields1 = array(
            'title' => 'test1',
            'content' => 'test_content',
            'userId' => $user1['id'],
            'classroomId' => $classroom['id'],
            'rating' => 1,
        );
        $this->getClassRoomReviewService()->saveReview($fields1);

        $fields2 = array(
            'title' => 'test2',
            'content' => 'test_content',
            'userId' => $user['id'],
            'classroomId' => $classroom['id'],
            'rating' => 2,
        );
        $this->getClassRoomReviewService()->saveReview($fields2);

        $results = $this->getClassRoomReviewService()->searchReviewCount(array('classroomId' => $classroom['id']));
        $this->assertEquals(2, $results);
    }

    public function testGetUserClassroomReviewWithExistId()
    {
        $user = $this->getCurrentUser();
        $user1 = $this->createStudentUser();

        $classroom = array(
            'title' => 'test',
        );
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $fields1 = array(
            'title' => 'test1',
            'content' => 'test_content',
            'userId' => $user1['id'],
            'classroomId' => $classroom['id'],
            'rating' => 1,
        );
        $this->getClassRoomReviewService()->saveReview($fields1);

        $fields2 = array(
            'title' => 'test2',
            'content' => 'test_content',
            'userId' => $user['id'],
            'classroomId' => $classroom['id'],
            'rating' => 2,
        );
        $this->getClassRoomReviewService()->saveReview($fields2);

        $result = $this->getClassRoomReviewService()->getUserClassroomReview($user['id'], $classroom['id']);
        $this->assertEquals('test2', $result['title']);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     */
    public function testGetUserClassroomReviewWithNotExistId()
    {
        $user = $this->getCurrentUser();
        $user1 = $this->createStudentUser();

        $classroom = array(
            'title' => 'test',
        );
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $fields1 = array(
            'title' => 'test1',
            'content' => 'test_content',
            'userId' => $user1['id'],
            'classroomId' => $classroom['id'],
            'rating' => 1,
        );
        $this->getClassRoomReviewService()->saveReview($fields1);

        $fields2 = array(
            'title' => 'test2',
            'content' => 'test_content',
            'userId' => $user['id'],
            'classroomId' => $classroom['id'],
            'rating' => 2,
        );
        $this->getClassRoomReviewService()->saveReview($fields2);

        $result = $this->getClassRoomReviewService()->getUserClassroomReview($user['id'], $classroom['id'] + 1);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomReviewException
     * @expectedExceptionMessage exception.classroom.review.no_more_than_5
     */
    public function testSaveReviewRatingError()
    {
        $fields1 = array(
            'title' => 'test1',
            'content' => 'test_content',
            'classroomId' => 123,
            'rating' => 6,
            'userId' => 1,
        );
        $this->getClassRoomReviewService()->saveReview($fields1);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testSaveReviewWithoutUserId()
    {
        $classroom = array(
            'title' => 'test',
        );
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $fields1 = array(
            'title' => 'test1',
            'content' => 'test_content',
            'classroomId' => $classroom['id'],
            'rating' => 1,
        );
        $this->getClassRoomReviewService()->saveReview($fields1);

        $fields2 = array(
            'title' => 'test2',
            'content' => 'test_content',
            'classroomId' => $classroom['id'],
            'rating' => 2,
        );
        $this->getClassRoomReviewService()->saveReview($fields2);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomException
     * @expectedExceptionMessage exception.classroom.not_found
     */
    public function testSaveReviewWithNotExistClassroom()
    {
        $user1 = $this->createStudentUser();

        $classroom = array(
            'title' => 'test',
        );

        $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'tryTakeClassroom',
                'returnValue' => array(),
            ),
        ));

        $fields1 = array(
            'title' => 'test1',
            'content' => 'test_content',
            'userId' => $user1['id'],
            'classroomId' => 123,
            'rating' => 1,
        );
        $this->getClassRoomReviewService()->saveReview($fields1);
    }

    /**
     * @expectedException \Biz\User\UserException
     */
    public function testSaveReviewWithNotExistUser()
    {
        $user = $this->getCurrentUser();

        $classroom = array(
            'title' => 'test',
        );
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $fields1 = array(
            'title' => 'test1',
            'content' => 'test_content',
            'userId' => $user['id'] + 10,
            'classroomId' => $classroom['id'],
            'rating' => 1,
        );
        $this->getClassRoomReviewService()->saveReview($fields1);
    }

    public function testSaveReviewWithExistReview()
    {
        $user = $this->getCurrentUser();

        $classroom = array(
            'title' => 'test',
        );
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $fields1 = array(
            'title' => 'test1',
            'content' => 'test_content',
            'userId' => $user['id'],
            'classroomId' => $classroom['id'],
            'rating' => 1,
        );
        $this->getClassRoomReviewService()->saveReview($fields1);
        $fields1['title'] = 'test2';
        $result = $this->getClassRoomReviewService()->saveReview($fields1);

        $this->assertEquals('test2', $result['title']);
    }

    public function testDeleteReviewWithExistReview()
    {
        $user = $this->getCurrentUser();

        $classroom = array(
            'title' => 'test',
        );
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $fields1 = array(
            'title' => 'test1',
            'content' => 'test_content',
            'userId' => $user['id'],
            'classroomId' => $classroom['id'],
            'rating' => 1,
        );
        $review = $this->getClassRoomReviewService()->saveReview($fields1);
        $this->getClassRoomReviewService()->deleteReview($review['id']);
        $review = $this->getClassRoomReviewService()->getReview($review['id']);
        $this->assertNull($review);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomReviewException
     */
    public function testDeleteReviewWithNotExistReview()
    {
        $classroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $this->getClassRoomReviewService()->deleteReview(100);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.unlogin
     */
    public function testDeleteReviewUserUnlogin()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'),
            'org' => array('id' => 1),
        ));
        $biz = $this->getBiz();
        $biz['user'] = $currentUser;

        $this->getClassRoomReviewService()->deleteReview(123);
    }

    /**
     * @expectedException \Biz\Classroom\ClassroomReviewException
     * @expectedExceptionMessage exception.classroom.review.permission_denied
     */
    public function testDeleteReviewPermisstion()
    {
        $biz = $this->getBiz();
        $currentUser = $this->getCurrentuser();

        $classroom = array(
            'title' => 'test',
        );
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $fields1 = array(
            'title' => 'test1',
            'content' => 'test_content',
            'userId' => $currentUser['id'],
            'classroomId' => $classroom['id'],
            'rating' => 1,
        );
        $review = $this->getClassRoomReviewService()->saveReview($fields1);

        $user1 = $this->getUserService()->register(array(
            'nickname' => 'student',
            'email' => 'student@admin.com',
            'password' => 'student',
            'createdIp' => '127.0.0.1',
            'orgCode' => '1.',
            'orgId' => '1',
            'roles' => array('ROLE_USER', 'ROLE_TEACHER'),
        ));
        $user = new CurrentUser();
        $user->fromArray($user1);
        $biz['user'] = $user;

        $this->getClassRoomReviewService()->deleteReview($review['id']);
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return ClassroomReviewService
     */
    protected function getClassRoomReviewService()
    {
        return $this->createService('Classroom:ClassroomReviewService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    private function createStudentUser()
    {
        $user = array();
        $user['email'] = 'userStudent@userStudent.com';
        $user['nickname'] = 'userStudent';
        $user['password'] = 'userStudent';
        $user = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER');

        return $user;
    }

    private function createTeacherUser()
    {
        $user = array();
        $user['email'] = 'teacherUser@user.com';
        $user['nickname'] = 'teacherUser';
        $user['password'] = 'teacherUser';
        $user = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER', 'ROLE_TEACHER');

        return $user;
    }

    private function createNormalUser()
    {
        $user = array();
        $user['email'] = 'normal@user.com';
        $user['nickname'] = 'normal';
        $user['password'] = 'user';
        $user = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER');

        return $user;
    }
}
