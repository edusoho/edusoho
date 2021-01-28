<?php

namespace Tests\Unit\Review\Service;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseTestCase;
use Biz\Classroom\Dao\ClassroomDao;
use Biz\Common\CommonException;
use Biz\Course\Dao\CourseDao;
use Biz\Goods\Dao\GoodsDao;
use Biz\Product\Dao\ProductDao;
use Biz\Review\Dao\ReviewDao;
use Biz\Review\ReviewException;
use Biz\Review\Service\ReviewService;
use Biz\Role\Util\PermissionBuilder;
use Biz\User\CurrentUser;
use Topxia\Service\Common\ServiceKernel;

class ReviewServiceTest extends BaseTestCase
{
    public function testGetReview()
    {
        $expected = $this->createReview([]);

        $resultNull = $this->getReviewService()->getReview($expected['id'] + 100000);

        $this->assertNull($resultNull);

        $result = $this->getReviewService()->getReview($expected['id']);

        $this->assertEquals($expected, $result);
    }

    public function testTryCreateCourseReview_whenCannotTakeCourse_thenThrowException()
    {
        $this->expectException(ReviewException::class);
        $this->expectExceptionMessage('exception.review.forbidden_create_review');

        $mockedCourseService = $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'canTakeCourse',
                'returnValue' => false,
            ],
        ]);

        $this->getReviewService()->tryCreateReview($this->mockDefaultReview(['targetType' => 'course']));
        $mockedCourseService->shouldHaveReceived('getCourse')->times(1);
    }

    public function testTryCreateCourseReview()
    {
        $mockedCourseService = $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'canTakeCourse',
                'returnValue' => true,
            ],
        ]);

        $expected = $this->mockDefaultReview(['targetType' => 'course']);
        $result = $this->getReviewService()->tryCreateReview($expected);

        $this->assertEquals($expected, $result);
    }

    public function testTryCreateClassroomReview_whenCannotTakeClassroom_thenThrowException()
    {
        $this->expectException(ReviewException::class);
        $this->expectExceptionMessage('exception.review.forbidden_create_review');

        $mockedCourseService = $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'canTakeClassroom',
                'returnValue' => false,
            ],
        ]);

        $this->getReviewService()->tryCreateReview($this->mockDefaultReview(['targetType' => 'classroom']));
        $mockedCourseService->shouldHaveReceived('getCourse')->times(1);
    }

    public function testTryCreateClassroomReview()
    {
        $mockedCourseService = $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'canTakeClassroom',
                'returnValue' => true,
            ],
        ]);

        $expected = $this->mockDefaultReview(['targetType' => 'classroom']);
        $result = $this->getReviewService()->tryCreateReview($expected);

        $this->assertEquals($expected, $result);
    }

    public function testCreateReview_whenUserIdNotExist_thenThrowException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionMessage('exception.common_parameter_missing');
        $review = $this->mockDefaultReview();
        unset($review['userId']);
        $this->getReviewService()->createReview($review);
    }

    public function testCreateReview_whenRatingNotExist_thenThrowException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionMessage('exception.common_parameter_missing');
        $review = $this->mockDefaultReview();
        unset($review['rating']);
        $this->getReviewService()->createReview($review);
    }

    public function testCreateReview_whenTargetTypeNotExist_thenThrowException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionMessage('exception.common_parameter_missing');
        $review = $this->mockDefaultReview();
        unset($review['targetType']);
        $this->getReviewService()->createReview($review);
    }

    public function testCreateReview_whenTargetIdNotExist_thenThrowException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionMessage('exception.common_parameter_missing');
        $review = $this->mockDefaultReview();
        unset($review['targetId']);
        $this->getReviewService()->createReview($review);
    }

    public function testCreateReview_whenContentNotExist_thenThrowException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionMessage('exception.common_parameter_missing');
        $review = $this->mockDefaultReview();
        unset($review['content']);
        $this->getReviewService()->createReview($review);
    }

    public function testCreateReview_whenRatingInvalid_thenThrowException()
    {
        $this->expectException(ReviewException::class);
        $this->expectExceptionMessage('exception.review.rating_limit');
        $review = $this->mockDefaultReview();
        $review['rating'] = 100;
        $this->getReviewService()->createReview($review);
    }

    public function testCreateReview()
    {
        $result = $this->getReviewService()->createReview($this->mockDefaultReview());

        $expected = $this->getReviewService()->getReview(1);
        $this->assertEquals($expected, $result);
    }

    public function testGetByUserIdAndTargetTypeAndTargetId()
    {
        $review = $this->createReview();
        $resultNull = $this->getReviewService()->getReviewByUserIdAndTargetTypeAndTargetId($review['userId'] + 10000, $review['targetType'], $review['targetId']);
        $this->assertNull($resultNull);

        $result = $this->getReviewService()->getReviewByUserIdAndTargetTypeAndTargetId($review['userId'], $review['targetType'], $review['targetId']);
        $this->assertEquals($review, $result);
    }

    public function testUpdateReview_whenNotAllowed_thenThrowException()
    {
        $this->expectException(ReviewException::class);
        $this->expectExceptionMessage('exception.review.forbidden_operate_review');

        $review = $this->createReview();

        $this->setCurrentUser();

        $this->getReviewService()->updateReview($review['id'], []);
    }

    public function testUpdateReview()
    {
        $review = $this->createReview();

        $before = $this->getReviewService()->getReview($review['id']);
        $result = $this->getReviewService()->updateReview($review['id'], ['content' => 'test update', 'rating' => $before['rating'] - 1]);

        $this->assertNotEquals($before, $result);
        $this->assertNotEquals($before['rating'], $result['rating']);
        $this->assertEquals($review['content'], $before['content']);
        $this->assertEquals('test update', $result['content']);
    }

    public function testDelete_whenNotAllowed_thenThrowException()
    {
        $this->expectException(ReviewException::class);
        $this->expectExceptionMessage('exception.review.forbidden_operate_review');

        $review = $this->createReview();

        $this->setCurrentUser();

        $this->getReviewService()->deleteReview($review['id']);
    }

    public function testDeleteReview()
    {
        $review = $this->createReview();
        $review1 = $this->createReview(['parentId' => $review['id']]);
        $review2 = $this->createReview(['parentId' => $review['id']]);
        $review3 = $this->createReview(['parentId' => $review['id']]);
        $review4 = $this->createReview(['parentId' => $review['id']]);

        $before = $this->getReviewService()->getReview($review['id']);

        $before1 = $this->getReviewService()->getReview($review1['id']);
        $before2 = $this->getReviewService()->getReview($review2['id']);
        $before3 = $this->getReviewService()->getReview($review3['id']);
        $before4 = $this->getReviewService()->getReview($review4['id']);

        $this->getReviewService()->deleteReview($review['id']);

        $after = $this->getReviewService()->getReview($review['id']);

        $after1 = $this->getReviewService()->getReview($review1['id']);
        $after2 = $this->getReviewService()->getReview($review2['id']);
        $after3 = $this->getReviewService()->getReview($review3['id']);
        $after4 = $this->getReviewService()->getReview($review4['id']);

        $this->assertEquals($review, $before);
        $this->assertEquals($review1, $before1);
        $this->assertEquals($review2, $before2);
        $this->assertEquals($review3, $before3);
        $this->assertEquals($review4, $before4);

        $this->assertNull($after);
        $this->assertNull($after1);
        $this->assertNull($after2);
        $this->assertNull($after3);
        $this->assertNull($after4);
    }

    public function testCountReview()
    {
        $review1 = $this->createReview();
        $review2 = $this->createReview(['userId' => 23]);
        $review3 = $this->createReview(['targetType' => 'course']);
        $review4 = $this->createReview(['targetId' => '4']);
        $review5 = $this->createReview(['targetType' => 'course']);

        $count1 = $this->getReviewService()->countReviews(['targetType' => 'course']);
        $this->assertEquals(2, $count1);

        $count2 = $this->getReviewService()->countReviews(['targetId' => 1]);
        $this->assertEquals(4, $count2);

        $count3 = $this->getReviewService()->countReviews(['userId' => 23]);
        $this->assertEquals(1, $count3);

        $count4 = $this->getReviewService()->countReviews(['targetType' => 'goods', 'targetId' => 1]);
        $this->assertEquals(2, $count4);
    }

    public function testSearchReview_withDifferentConditions()
    {
        $review1 = $this->createReview();
        $review2 = $this->createReview(['userId' => 23]);
        $review3 = $this->createReview(['targetType' => 'course']);
        $review4 = $this->createReview(['targetId' => '4']);
        $review5 = $this->createReview(['targetType' => 'course']);

        $result1 = $this->getReviewService()->searchReviews(['userId' => 23], [], 0, 10);
        $this->assertEquals([$review2], $result1);

        $expected2 = ArrayToolkit::index([$review3, $review5], 'id');
        $result2 = $this->getReviewService()->searchReviews(['targetType' => 'course'], [], 0, 10);

        $result2 = ArrayToolkit::index($result2, 'id');
        $this->assertEquals($expected2, $result2);

        $expected3 = ArrayToolkit::index([$review1, $review2, $review3, $review5], 'id');
        $result3 = $this->getReviewService()->searchReviews(['targetId' => 1], [], 0, 10);
        $result3 = ArrayToolkit::index($result3, 'id');

        $this->assertEquals($expected3, $result3);

        $expected4 = ArrayToolkit::index([$review1, $review2], 'id');
        $result4 = $this->getReviewService()->searchReviews(['targetType' => 'goods', 'targetId' => 1], [], 0, 10);
        $result4 = ArrayToolkit::index($result4, 'id');

        $this->assertEquals($expected4, $result4);
    }

    public function testSearchReview_withDifferentOrderByAndLimits()
    {
        $review1 = $this->createReview();
        $review2 = $this->createReview(['userId' => 23]);
        $review3 = $this->createReview(['targetType' => 'course']);
        $review4 = $this->createReview(['targetId' => '4']);
        $review5 = $this->createReview(['targetType' => 'course']);

        $result1 = $this->getReviewService()->searchReviews(['userId' => 23], ['id' => 'desc'], 0, 10);
        $this->assertEquals([$review2], $result1);

        $result2 = $this->getReviewService()->searchReviews(['targetType' => 'course'], ['id' => 'desc'], 0, 10);
        $this->assertEquals([$review5, $review3], $result2);

        $result3 = $this->getReviewService()->searchReviews(['targetId' => 1], ['id' => 'desc'], 0, 10);
        $this->assertEquals([$review5, $review3, $review2, $review1], $result3);

        $result4 = $this->getReviewService()->searchReviews(['targetType' => 'goods', 'targetId' => 1], ['id' => 'desc'], 0, 10);
        $this->assertEquals([$review2, $review1], $result4);

        $result4 = $this->getReviewService()->searchReviews(['targetType' => 'goods', 'targetId' => 1], ['id' => 'desc'], 0, 1);
        $this->assertEquals([$review2], $result4);
    }

    public function testSearchReview_withDifferentColumns()
    {
        $review1 = $this->createReview();
        $review2 = $this->createReview(['userId' => 23]);
        $review3 = $this->createReview(['targetType' => 'course']);
        $review4 = $this->createReview(['targetId' => '4']);
        $review5 = $this->createReview(['targetType' => 'course']);

        $expected1 = [
            [
                'targetType' => $review2['targetType'],
                'targetId' => $review2['targetId'],
                'userId' => $review2['userId'],
            ],
        ];

        $result1 = $this->getReviewService()->searchReviews(['userId' => 23], ['id' => 'desc'], 0, 10, ['targetType', 'targetId', 'userId']);
        $this->assertEquals($expected1, $result1);

        $expected2 = [
            [
                'targetType' => $review5['targetType'],
                'targetId' => $review5['targetId'],
                'content' => $review5['content'],
            ],
            [
                'targetType' => $review3['targetType'],
                'targetId' => $review3['targetId'],
                'content' => $review3['content'],
            ],
        ];
        $result2 = $this->getReviewService()->searchReviews(['targetType' => 'course'], ['id' => 'desc'], 0, 10, ['targetType', 'targetId', 'content']);
        $this->assertEquals($expected2, $result2);
    }

    public function testCountRatingByTargetTypeAndTargetId()
    {
        $review = $this->createReview();
        $review2 = $this->createReview(['targetType' => $review['targetType'].'test']);
        $review3 = $this->createReview(['rating' => 3]);

        $result = $this->getReviewService()->countRatingByTargetTypeAndTargetId($review['targetType'], $review['targetId']);

        $this->assertEquals([
            'ratingNum' => 2,
            'rating' => ($review['rating'] + $review3['rating']) / 2,
        ], $result);
    }

    public function testSumRatingByConditions()
    {
        $review1 = $this->createReview();
        $review2 = $this->createReview(['rating' => 1, 'targetId' => $review1['targetId'] + 1000]);
        $review3 = $this->createReview(['rating' => 3]);

        $result = $this->getReviewDao()->sumRatingByConditions(['targetId' => $review1['targetId']]);
        $this->assertEquals($review1['rating'] + $review3['rating'], $result);
    }

    public function testDeleteByParentId()
    {
        $review = $this->createReview();
        $review1 = $this->createReview(['parentId' => $review['id']]);

        $before = $this->getReviewDao()->get($review1['id']);

        $this->getReviewDao()->deleteByParentId($review1['parentId']);

        $after = $this->getReviewDao()->get($review1['id']);
        $this->assertEquals($review1, $before);
        $this->assertNull($after);
    }

    public function testDeleteByTargetTypeAndTargetId()
    {
        $review = $this->createReview();
        $review1 = $this->createReview(['targetId' => 1000]);

        $before = $this->getReviewDao()->get($review1['id']);

        $this->getReviewDao()->deleteByTargetTypeAndTargetId($review1['targetType'], $review1['targetId']);

        $after = $this->getReviewDao()->get($review1['id']);
        $this->assertEquals($review1, $before);
        $this->assertNull($after);
    }

    public function testCountCourseReview()
    {
        list($course1, $review1) = $this->createCourseReviews();
        list($course1, $review2) = $this->createCourseReviews($course1, ['content' => 'review2', 'userId' => 1000]);
        list($course1, $review3) = $this->createCourseReviews($course1, ['content' => 'review3', 'parentId' => $review1['id']]);

        list($course2, $review4) = $this->createCourseReviews(['courseSetId' => 2, 'courseSetTitle' => 'title test'], ['userId' => 1000, 'content' => 'review3']);
        list($course2, $review5) = $this->createCourseReviews($course2, ['content' => 'review4', 'rating' => 1]);

        list($course3, $review6) = $this->createCourseReviews(['parentId' => 2], ['content' => 'review5']);
        list($course3, $review7) = $this->createCourseReviews($course3, ['content' => 'review6']);

        $result1 = $this->getReviewService()->countCourseReviews(['userId' => $this->getCurrentUser()->getId()]);
        $this->assertEquals(5, $result1);

        $result2 = $this->getReviewService()->countCourseReviews(['courseTitle' => $course2['courseSetTitle']]);
        $this->assertEquals(2, $result2);

        $result3 = $this->getReviewService()->countCourseReviews(['courseTitle' => 'test']);
        $this->assertEquals(7, $result3);

        $result4 = $this->getReviewService()->countCourseReviews(['rating' => 1]);
        $this->assertEquals(1, $result4);

        $result5 = $this->getReviewService()->countCourseReviews(['parentId' => 0]);
        $this->assertEquals(6, $result5);
    }

    public function testSearchCourseReview_withDifferentConditions()
    {
        list($course1, $review1) = $this->createCourseReviews();
        list($course1, $review2) = $this->createCourseReviews($course1, ['content' => 'review2', 'userId' => 1000]);
        list($course1, $review3) = $this->createCourseReviews($course1, ['content' => 'review3', 'parentId' => $review1['id']]);

        list($course2, $review4) = $this->createCourseReviews(['courseSetId' => 2, 'courseSetTitle' => 'title test'], ['userId' => 1000, 'content' => 'review3']);
        list($course2, $review5) = $this->createCourseReviews($course2, ['content' => 'review4', 'rating' => 1]);

        list($course3, $review6) = $this->createCourseReviews(['parentId' => 2], ['content' => 'review5']);
        list($course3, $review7) = $this->createCourseReviews($course3, ['content' => 'review6']);

        $result1 = $this->getReviewService()->searchCourseReviews(['userId' => $this->getCurrentUser()->getId()], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review1, $review3, $review5, $review6, $review7], $result1);

        $result2 = $this->getReviewService()->searchCourseReviews(['courseTitle' => $course2['courseSetTitle']], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review4, $review5], $result2);

        $result3 = $this->getReviewService()->searchCourseReviews(['courseTitle' => 'test'], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review1, $review2, $review3, $review4, $review5, $review6, $review7], $result3);

        $result4 = $this->getReviewService()->searchCourseReviews(['rating' => 1], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review5], $result4);

        $result5 = $this->getReviewService()->searchCourseReviews(['parentId' => 0], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review1, $review2, $review4, $review5, $review6, $review7], $result5);
    }

    public function testSearchCourseReview_withDifferentOrderByAndLimits()
    {
        list($course1, $review1) = $this->createCourseReviews();
        list($course1, $review2) = $this->createCourseReviews($course1, ['content' => 'review2', 'userId' => 1000]);
        list($course1, $review3) = $this->createCourseReviews($course1, ['content' => 'review3', 'parentId' => $review1['id']]);

        list($course2, $review4) = $this->createCourseReviews(['courseSetId' => 2, 'courseSetTitle' => 'title test'], ['userId' => 1000, 'content' => 'review3']);
        list($course2, $review5) = $this->createCourseReviews($course2, ['content' => 'review4', 'rating' => 1]);

        list($course3, $review6) = $this->createCourseReviews(['parentId' => 2], ['content' => 'review5']);
        list($course3, $review7) = $this->createCourseReviews($course3, ['content' => 'review6']);

        $result1 = $this->getReviewService()->searchCourseReviews(['courseTitle' => 'test'], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review1, $review2, $review3, $review4, $review5, $review6, $review7], $result1);

        $result2 = $this->getReviewService()->searchCourseReviews(['courseTitle' => 'test'], ['id' => 'DESC'], 0, 10);
        $this->assertEquals([$review7, $review6, $review5, $review4, $review3, $review2, $review1], $result2);

        $result1 = $this->getReviewService()->searchCourseReviews(['courseTitle' => 'test'], ['id' => 'ASC'], 0, 5);
        $this->assertEquals([$review1, $review2, $review3, $review4, $review5], $result1);

        $result2 = $this->getReviewService()->searchCourseReviews(['courseTitle' => 'test'], ['id' => 'DESC'], 0, 3);
        $this->assertEquals([$review7, $review6, $review5], $result2);

        $result1 = $this->getReviewService()->searchCourseReviews(['courseTitle' => 'test'], ['id' => 'ASC'], 2, 2);
        $this->assertEquals([$review3, $review4], $result1);
    }

    public function testCountClassroomReview()
    {
        list($classroom1, $review1) = $this->createClassroomReviews();
        list($classroom1, $review2) = $this->createClassroomReviews($classroom1, ['content' => 'review2', 'userId' => 1000]);
        list($classroom1, $review3) = $this->createClassroomReviews($classroom1, ['content' => 'review3', 'parentId' => $review1['id']]);

        list($classroom2, $review4) = $this->createClassroomReviews(['title' => 'title test classroom'], ['userId' => 1000, 'content' => 'review3']);
        list($classroom2, $review5) = $this->createClassroomReviews($classroom2, ['content' => 'review4', 'rating' => 1]);

        $result1 = $this->getReviewService()->countClassroomReviews(['userId' => $this->getCurrentUser()->getId()]);
        $this->assertEquals(3, $result1);

        $result2 = $this->getReviewService()->countClassroomReviews(['classroomTitle' => $classroom2['title']]);
        $this->assertEquals(2, $result2);

        $result3 = $this->getReviewService()->countClassroomReviews(['classroomTitle' => 'classroom']);
        $this->assertEquals(5, $result3);

        $result4 = $this->getReviewService()->countClassroomReviews(['rating' => 1]);
        $this->assertEquals(1, $result4);

        $result5 = $this->getReviewService()->countClassroomReviews(['parentId' => 0]);
        $this->assertEquals(4, $result5);
    }

    public function testSearchClassroomReview_withDifferentConditions()
    {
        list($classroom1, $review1) = $this->createClassroomReviews();
        list($classroom1, $review2) = $this->createClassroomReviews($classroom1, ['content' => 'review2', 'userId' => 1000]);
        list($classroom1, $review3) = $this->createClassroomReviews($classroom1, ['content' => 'review3', 'parentId' => $review1['id']]);

        list($classroom2, $review4) = $this->createClassroomReviews(['title' => 'title test classroom'], ['userId' => 1000, 'content' => 'review3']);
        list($classroom2, $review5) = $this->createClassroomReviews($classroom2, ['content' => 'review4', 'rating' => 1]);

        $result1 = $this->getReviewService()->searchClassroomReviews(['userId' => $this->getCurrentUser()->getId()], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review1, $review3, $review5], $result1);

        $result2 = $this->getReviewService()->searchClassroomReviews(['classroomTitle' => $classroom2['title']], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review4, $review5], $result2);

        $result3 = $this->getReviewService()->searchClassroomReviews(['classroomTitle' => 'classroom'], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review1, $review2, $review3, $review4, $review5], $result3);

        $result4 = $this->getReviewService()->searchClassroomReviews(['rating' => 1], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review5], $result4);

        $result5 = $this->getReviewService()->searchClassroomReviews(['parentId' => 0], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review1, $review2, $review4, $review5], $result5);
    }

    public function testSearchClassroomReview_withDifferentOrderByAndLimit()
    {
        list($classroom1, $review1) = $this->createClassroomReviews();
        list($classroom1, $review2) = $this->createClassroomReviews($classroom1, ['content' => 'review2', 'userId' => 1000]);
        list($classroom1, $review3) = $this->createClassroomReviews($classroom1, ['content' => 'review3', 'parentId' => $review1['id']]);

        list($classroom2, $review4) = $this->createClassroomReviews(['title' => 'title test classroom'], ['userId' => 1000, 'content' => 'review3']);
        list($classroom2, $review5) = $this->createClassroomReviews($classroom2, ['content' => 'review4', 'rating' => 1]);

        $result1 = $this->getReviewService()->searchClassroomReviews(['classroomTitle' => 'classroom'], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review1, $review2, $review3, $review4, $review5], $result1);

        $result2 = $this->getReviewService()->searchClassroomReviews(['classroomTitle' => 'classroom'], ['id' => 'DESC'], 0, 10);
        $this->assertEquals([$review5, $review4, $review3, $review2, $review1], $result2);

        $result3 = $this->getReviewService()->searchClassroomReviews(['classroomTitle' => 'classroom'], ['id' => 'ASC'], 0, 3);
        $this->assertEquals([$review1, $review2, $review3], $result3);

        $result4 = $this->getReviewService()->searchClassroomReviews(['classroomTitle' => 'classroom'], ['id' => 'DESC'], 0, 3);
        $this->assertEquals([$review5, $review4, $review3], $result4);
    }

    protected function createReview($fields = [])
    {
        $review = $this->mockDefaultReview($fields);

        return $this->getReviewDao()->create($review);
    }

    protected function mockDefaultReview($fields = [])
    {
        return array_merge([
            'userId' => $this->getCurrentUser()->getId(),
            'targetType' => 'goods',
            'targetId' => '1',
            'rating' => '5',
            'content' => 'test content',
            'parentId' => '0',
        ], $fields);
    }

    private function setCurrentUser($user = [])
    {
        $currentUser = new CurrentUser();

        if (empty($user)) {
            $user = [
                'id' => 0,
                'nickname' => '游客',
                'currentIp' => '',
                'roles' => [],
            ];
        }

        $currentUser->fromArray($user);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        ServiceKernel::instance()->setCurrentUser($currentUser);
    }

    protected function createClassroomReviews($classroom = [], $review = [])
    {
        if (empty($classroom['id'])) {
            $classroom = $this->getClassroomDao()->create(array_merge([
                'title' => 'classroom title',
                'creator' => $this->getCurrentUser()->getId(),
            ], $classroom));
        } else {
            $classroom = $this->getClassroomDao()->get($classroom['id']);
        }

        $product = $this->getProductDao()->getByTargetIdAndType($classroom['id'], 'classroom');
        if (empty($product)) {
            $product = $this->getProductDao()->create([
                'targetType' => 'classroom',
                'targetId' => $classroom['id'],
                'title' => $classroom['title'],
                'owner' => $classroom['creator'],
            ]);
        }

        $goods = $this->getGoodsDao()->getByProductId($product['id']);
        if (empty($goods)) {
            $goods = $this->getGoodsDao()->create([
                'productId' => $product['id'],
                'type' => 'classroom',
                'title' => $product['title'],
                'creator' => $product['owner'],
            ]);
        }

        $review = $this->createReview(array_merge([
            'userId' => $this->getCurrentUser()->getId(),
            'targetType' => 'goods',
            'targetId' => $goods['id'],
            'rating' => 5,
            'content' => 'test review content',
            'parentId' => 0,
        ], $review));

        return [$classroom, $review];
    }

    protected function createCourseReviews($course = [], $review = [])
    {
        if (empty($course['id'])) {
            $course = $this->getCourseDao()->create(array_merge([
                'courseSetId' => 1,
                'courseSetTitle' => 'course-set test title',
                'parentId' => 0,
                'creator' => 100,
            ], $course));
        } else {
            $course = $this->getCourseDao()->get($course['id']);
        }

        if (0 == $course['parentId']) {
            $product = $this->getProductDao()->getByTargetIdAndType($course['courseSetId'], 'course');

            if (empty($product)) {
                $product = $this->getProductDao()->create([
                    'targetType' => 'course',
                    'targetId' => $course['courseSetId'],
                    'title' => $course['courseSetTitle'],
                    'owner' => $course['creator'],
                ]);
            }

            $goods = $this->getGoodsDao()->getByProductId($product['id']);

            if (empty($goods)) {
                $goods = $this->getGoodsDao()->create([
                    'productId' => $product['id'],
                    'type' => 'course',
                    'title' => $product['title'],
                    'creator' => $product['owner'],
                ]);
            }
        }

        $review = $this->createReview(array_merge([
            'userId' => $this->getCurrentUser()->getId(),
            'targetType' => empty($goods) ? 'course' : 'goods',
            'targetId' => empty($goods) ? $course['id'] : $goods['id'],
            'rating' => 5,
            'content' => 'test review content',
            'parentId' => 0,
        ], $review));

        return [$course, $review];
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }

    /**
     * @return ClassroomDao
     */
    protected function getClassroomDao()
    {
        return $this->createDao('Classroom:ClassroomDao');
    }

    /**
     * @return ProductDao
     */
    protected function getProductDao()
    {
        return $this->createDao('Product:ProductDao');
    }

    /**
     * @return GoodsDao
     */
    protected function getGoodsDao()
    {
        return $this->createDao('Goods:GoodsDao');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->createService('Review:ReviewService');
    }

    /**
     * @return ReviewDao
     */
    protected function getReviewDao()
    {
        return $this->createDao('Review:ReviewDao');
    }
}
