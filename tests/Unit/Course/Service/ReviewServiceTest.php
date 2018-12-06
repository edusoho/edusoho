<?php

namespace Tests\Unit\Course\Service;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;

class ReviewServiceTest extends BaseTestCase
{
    public function testGetReview()
    {
        $course = $this->createCourse();
        $review = $this->createReview($course['id']);

        $result = $this->getReviewService()->getReview($review['id']);

        $this->assertArrayEquals($review, $result);
    }

    public function testFindCourseReviews()
    {
        $course1 = $this->createCourse();
        $course2 = $this->createCourse1();

        $review1 = $this->createReview($course1['id']);
        $review2 = $this->createReview($course2['id']);

        $reviews = $this->getReviewService()->findCourseReviews($course1['id'], 0, 1);

        $this->assertEquals(1, count($reviews));
    }

    public function testGetCourseReviewCount()
    {
        $course1 = $this->createCourse();
        $course2 = $this->createCourse1();

        $review1 = $this->createReview($course1['id']);
        $review2 = $this->createReview($course2['id']);

        $count = $this->getReviewService()->getCourseReviewCount($course2['id']);

        $this->assertEquals(1, $count);
    }

    public function testGetUserCourseReview()
    {
        $course1 = $this->createCourse();
        $course2 = $this->createCourse1();

        $review1 = $this->createReview($course1['id']);
        $review2 = $this->createReview($course2['id']);

        $user = $this->getCurrentUser();

        $result = $this->getReviewService()->getUserCourseReview($user['id'], $course1['id']);

        $this->assertArrayEquals($review1, $result);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.not_found
     */
    public function testGetUserCourseReviewEmptyUser()
    {
        $result = $this->getReviewService()->getUserCourseReview(123, 1);
    }

    /**
     * @expectedException \Biz\Course\CourseException
     * @expectedExceptionMessage exception.course.not_found
     */
    public function testGetUserCourseReviewEmptyCourse()
    {
        $user = $this->getCurrentUser();

        $result = $this->getReviewService()->getUserCourseReview($user['id'], 123);
    }

    public function testSearchReviews()
    {
        $course1 = $this->createCourse();
        $course2 = $this->createCourse1();

        $review1 = $this->createReview($course1['id']);
        $review2 = $this->createReview($course2['id']);

        $conditions = array(
            'courseId' => $course1['id'],
        );
        $reviews = $this->getReviewService()->searchReviews($conditions, array('createdTime' => 'DESC'), 0, 10);
        $this->assertEquals(1, count($reviews));

        $reviews = $this->getReviewService()->searchReviews(array('content' => 'review content'), 'latest', 0, 10);
        $this->assertEquals(2, count($reviews));

        $reviews = $this->getReviewService()->searchReviews(array('author' => 'nickname'), 'rating', 0, 10);
        $this->assertEquals(0, count($reviews));
    }

    public function testSearchReviewsCount()
    {
        $course1 = $this->createCourse();
        $course2 = $this->createCourse1();

        $review1 = $this->createReview($course1['id']);
        $review2 = $this->createReview($course2['id']);

        $conditions = array(
            'courseId' => $course1['id'],
        );
        $count = $this->getReviewService()->searchReviewsCount($conditions);

        $this->assertEquals(1, $count);
    }

    public function testSaveReview()
    {
        $course = $this->createCourse();

        $fields = array(
            'courseId' => $course['id'],
            'rating' => 3,
            'parentId' => 0,
            'userId' => $this->getCurrentUser()->getId(),
            'content' => 'review content',
            'courseSetId' => 1,
            'createdTime' => time(),
        );
        $review = $this->getReviewService()->saveReview($fields);

        $this->assertEquals(1, $review['private']);
        $this->assertEquals($fields['content'], $review['content']);
        $this->assertEquals($fields['rating'], $review['rating']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testSaveReviewFieldsError()
    {
        $fields = array(
            'courseId' => 123,
            'rating' => 3,
        );
        $this->getReviewService()->saveReview($fields);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_error
     */
    public function testSaveReviewFieldsRatingError()
    {
        $fields = array(
            'courseId' => 123,
            'rating' => 6,
            'parentId' => 0,
            'userId' => $this->getCurrentUser()->getId(),
            'content' => 'review content',
            'courseSetId' => 1,
            'createdTime' => time(),
        );
        $this->getReviewService()->saveReview($fields);
    }

    /**
     * @expectedException \Biz\Course\CourseException
     * @expectedExceptionMessage exception.course.not_found
     */
    public function testSaveReviewFieldsCourseEmpty()
    {
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'tryTakeCourse',
                'returnValue' => array(array(), array()),
            ),
        ));

        $fields = array(
            'courseId' => 123,
            'rating' => 3,
            'parentId' => 0,
            'userId' => $this->getCurrentUser()->getId(),
            'content' => 'review content',
            'courseSetId' => 1,
            'createdTime' => time(),
        );
        $this->getReviewService()->saveReview($fields);
    }

    /**
     * @expectedException \Biz\User\UserException
     */
    public function testSaveReviewFieldsUserEmpty()
    {
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'tryTakeCourse',
                'returnValue' => array(array('id' => 1, 'courseSetId' => 1, 'status' => 'published'), array('learnedNum' => 1)),
            ),
        ));

        $fields = array(
            'courseId' => 1,
            'rating' => 3,
            'parentId' => 0,
            'userId' => 123,
            'content' => 'review content',
            'courseSetId' => 1,
            'createdTime' => time(),
        );
        $this->getReviewService()->saveReview($fields);
    }

    public function testDeleteReview()
    {
        $course = $this->createCourse();
        $review = $this->createReview($course['id']);
        $this->assertNotNull($review);

        $this->getReviewService()->deleteReview($review['id']);
        $result = $this->getReviewService()->getReview($review['id']);

        $this->assertNull($result);
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

        $this->getReviewService()->deleteReview(123);
    }

    /**
     * @expectedException \Biz\Course\ReviewException
     * @expectedExceptionMessage exception.review.not_found
     */
    public function testDeleteReviewEmpty()
    {
        $this->getReviewService()->deleteReview(123);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.permission_denied
     */
    public function testDeleteReviewPermisstion()
    {
        $biz = $this->getBiz();
        $currentUser = $this->getCurrentuser();

        $course1 = $this->createCourse();
        $fields = array(
            'courseId' => $course1['id'],
            'rating' => 3,
            'parentId' => 0,
            'content' => 'review content',
            'userId' => $currentUser['id'],
            'createdTime' => time(),
        );
        $review = $this->getReviewService()->saveReview($fields);

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

        $this->getReviewService()->deleteReview($review['id']);
    }

    public function testCountRatingByCourseId()
    {
        $course1 = $this->createCourse();

        $review1 = $this->createReview($course1['id']);
        $review2 = $this->createReview($course1['id']);

        $result = $this->getReviewService()->countRatingByCourseId($course1['id']);

        $this->assertEquals(1, $result['ratingNum']);
        $this->assertEquals(3, $result['rating']);
    }

    public function testCountRatingByCourseSetId()
    {
        $course1 = $this->createCourse();
        $course2 = $this->createCourse1($course1['courseSetId']);

        $review1 = $this->createReview($course1['id']);
        $review2 = $this->createReview($course2['id']);

        $result = $this->getReviewService()->countRatingByCourseSetId($course1['courseSetId']);

        $this->assertEquals(2, $result['ratingNum']);
        $this->assertEquals(3, $result['rating']);
    }

    protected function createReview($courseId)
    {
        $fields = array(
            'courseId' => $courseId,
            'rating' => 3,
            'parentId' => 0,
            'content' => 'review content',
            'userId' => $this->getCurrentUser()->getId(),
            'createdTime' => time(),
        );

        return $this->getReviewService()->saveReview($fields);
    }

    protected function createCourse()
    {
        $courseSetFields = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSetFields);

        $course = array(
            'title' => '第一个教学计划',
            'courseSetId' => $courseSet['id'],
            'learnMode' => 'lockMode',
            'expiryMode' => 'forever',
            'courseType' => 'normal',
        );

        return $this->getCourseService()->createCourse($course);
    }

    protected function createCourse1($courseSetId = 1)
    {
        $course = array(
            'title' => '第二个教学计划',
            'courseSetId' => $courseSetId,
            'learnMode' => 'lockMode',
            'expiryMode' => 'forever',
            'courseType' => 'normal',
        );

        return $this->getCourseService()->createCourse($course);
    }

    protected function getReviewService()
    {
        return $this->createService('Course:ReviewService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
