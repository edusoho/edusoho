<?php

namespace Tests\Course;

use Biz\BaseTestCase;

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

    public function testSearchReviews()
    {
        $course1 = $this->createCourse();
        $course2 = $this->createCourse1();

        $review1 = $this->createReview($course1['id']);
        $review2 = $this->createReview($course2['id']);

        $conditions = array(
            'courseId' => $course1['id']
        );

        $reviews = $this->getReviewService()->searchReviews($conditions, array('createdTime' => 'DESC'), 0, 10);

        $this->assertEquals(1, count($reviews));
    }

    public function testSearchReviewsCount()
    {
        $course1 = $this->createCourse();
        $course2 = $this->createCourse1();

        $review1 = $this->createReview($course1['id']);
        $review2 = $this->createReview($course2['id']);

        $conditions = array(
            'courseId' => $course1['id']
        );
        $count = $this->getReviewService()->searchReviewsCount($conditions);

        $this->assertEquals(1, $count);
    }

    public function testSaveReview()
    {
        $course = $this->createCourse();

        $fields = array(
            'courseId'    => $course['id'],
            'rating'      => 3,
            'parentId'    => 0,
            'content'     => 'review content',
            'courseSetId' => 1,
            'createdTime' => time()
        );
        $review = $this->getReviewService()->saveReview($fields);

        $this->assertEquals(1, $review['private']);
        $this->assertEquals($fields['content'], $review['content']);
        $this->assertEquals($fields['rating'], $review['rating']);
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
        $course2 = $this->createCourse1();

        $review1 = $this->createReview($course1['id']);
        $review2 = $this->createReview($course2['id']);

        $result = $this->getReviewService()->countRatingByCourseSetId($course1['courseSetId']);

        $this->assertEquals(2, $result['ratingNum']);
        $this->assertEquals(3, $result['rating']);
    }

    protected function createReview($courseId)
    {
        $fields = array(
            'courseId'    => $courseId,
            'rating'      => 3,
            'parentId'    => 0,
            'content'     => 'review content',
            'createdTime' => time()
        );
        return $this->getReviewService()->saveReview($fields);
    }

    protected function createCourse()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1,
            'learnMode'   => 'lockMode',
            'expiryMode'  => 'days',
            'expiryDays'  => 0
        );

        return $this->getCourseService()->createCourse($course);
    }

    protected function createCourse1()
    {
        $course = array(
            'title'       => '第二个教学计划',
            'courseSetId' => 1,
            'learnMode'   => 'lockMode',
            'expiryMode'  => 'days',
            'expiryDays'  => 0
        );

        return $this->getCourseService()->createCourse($course);
    }

    protected function getReviewService()
    {
        return $this->getBiz()->service('Course:ReviewService');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }
}
