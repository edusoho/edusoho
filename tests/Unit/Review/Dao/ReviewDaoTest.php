<?php

namespace Tests\Unit\Review\Dao;

use Biz\BaseTestCase;
use Biz\Classroom\Dao\ClassroomDao;
use Biz\Course\Dao\CourseDao;
use Biz\Goods\Dao\GoodsDao;
use Biz\Product\Dao\ProductDao;
use Biz\Review\Dao\ReviewDao;

class ReviewDaoTest extends BaseTestCase
{
    public function testGetByUserIdAndTargetTypeAndTargetId()
    {
        $expected = $this->createReview();
        $resultNull = $this->getReviewDao()->getByUserIdAndTargetTypeAndTargetId($expected['userId'] + 10000, $expected['targetType'], $expected['targetId']);

        $this->assertNull($resultNull);

        $result = $this->getReviewDao()->getByUserIdAndTargetTypeAndTargetId($expected['userId'], $expected['targetType'], $expected['targetId']);
        $this->assertEquals($expected, $result);
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

        $result1 = $this->getReviewDao()->countCourseReviews(['userId' => $this->getCurrentUser()->getId()]);
        $this->assertEquals(5, $result1);

        $result2 = $this->getReviewDao()->countCourseReviews(['courseTitle' => $course2['courseSetTitle']]);
        $this->assertEquals(2, $result2);

        $result3 = $this->getReviewDao()->countCourseReviews(['courseTitle' => 'test']);
        $this->assertEquals(7, $result3);

        $result4 = $this->getReviewDao()->countCourseReviews(['rating' => 1]);
        $this->assertEquals(1, $result4);

        $result5 = $this->getReviewDao()->countCourseReviews(['parentId' => 0]);
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

        $result1 = $this->getReviewDao()->searchCourseReviews(['userId' => $this->getCurrentUser()->getId()], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review1, $review3, $review5, $review6, $review7], $result1);

        $result2 = $this->getReviewDao()->searchCourseReviews(['courseTitle' => $course2['courseSetTitle']], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review4, $review5], $result2);

        $result3 = $this->getReviewDao()->searchCourseReviews(['courseTitle' => 'test'], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review1, $review2, $review3, $review4, $review5, $review6, $review7], $result3);

        $result4 = $this->getReviewDao()->searchCourseReviews(['rating' => 1], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review5], $result4);

        $result5 = $this->getReviewDao()->searchCourseReviews(['parentId' => 0], ['id' => 'ASC'], 0, 10);
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

        $result1 = $this->getReviewDao()->searchCourseReviews(['courseTitle' => 'test'], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review1, $review2, $review3, $review4, $review5, $review6, $review7], $result1);

        $result2 = $this->getReviewDao()->searchCourseReviews(['courseTitle' => 'test'], ['id' => 'DESC'], 0, 10);
        $this->assertEquals([$review7, $review6, $review5, $review4, $review3, $review2, $review1], $result2);

        $result1 = $this->getReviewDao()->searchCourseReviews(['courseTitle' => 'test'], ['id' => 'ASC'], 0, 5);
        $this->assertEquals([$review1, $review2, $review3, $review4, $review5], $result1);

        $result2 = $this->getReviewDao()->searchCourseReviews(['courseTitle' => 'test'], ['id' => 'DESC'], 0, 3);
        $this->assertEquals([$review7, $review6, $review5], $result2);

        $result1 = $this->getReviewDao()->searchCourseReviews(['courseTitle' => 'test'], ['id' => 'ASC'], 2, 2);
        $this->assertEquals([$review3, $review4], $result1);
    }

    public function testCountClassroomReview()
    {
        list($classroom1, $review1) = $this->createClassroomReviews();
        list($classroom1, $review2) = $this->createClassroomReviews($classroom1, ['content' => 'review2', 'userId' => 1000]);
        list($classroom1, $review3) = $this->createClassroomReviews($classroom1, ['content' => 'review3', 'parentId' => $review1['id']]);

        list($classroom2, $review4) = $this->createClassroomReviews(['title' => 'title test classroom'], ['userId' => 1000, 'content' => 'review3']);
        list($classroom2, $review5) = $this->createClassroomReviews($classroom2, ['content' => 'review4', 'rating' => 1]);

        $result1 = $this->getReviewDao()->countClassroomReviews(['userId' => $this->getCurrentUser()->getId()]);
        $this->assertEquals(3, $result1);

        $result2 = $this->getReviewDao()->countClassroomReviews(['classroomTitle' => $classroom2['title']]);
        $this->assertEquals(2, $result2);

        $result3 = $this->getReviewDao()->countClassroomReviews(['classroomTitle' => 'classroom']);
        $this->assertEquals(5, $result3);

        $result4 = $this->getReviewDao()->countClassroomReviews(['rating' => 1]);
        $this->assertEquals(1, $result4);

        $result5 = $this->getReviewDao()->countClassroomReviews(['parentId' => 0]);
        $this->assertEquals(4, $result5);
    }

    public function testSearchClassroomReview_withDifferentConditions()
    {
        list($classroom1, $review1) = $this->createClassroomReviews();
        list($classroom1, $review2) = $this->createClassroomReviews($classroom1, ['content' => 'review2', 'userId' => 1000]);
        list($classroom1, $review3) = $this->createClassroomReviews($classroom1, ['content' => 'review3', 'parentId' => $review1['id']]);

        list($classroom2, $review4) = $this->createClassroomReviews(['title' => 'title test classroom'], ['userId' => 1000, 'content' => 'review3']);
        list($classroom2, $review5) = $this->createClassroomReviews($classroom2, ['content' => 'review4', 'rating' => 1]);

        $result1 = $this->getReviewDao()->searchClassroomReviews(['userId' => $this->getCurrentUser()->getId()], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review1, $review3, $review5], $result1);

        $result2 = $this->getReviewDao()->searchClassroomReviews(['classroomTitle' => $classroom2['title']], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review4, $review5], $result2);

        $result3 = $this->getReviewDao()->searchClassroomReviews(['classroomTitle' => 'classroom'], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review1, $review2, $review3, $review4, $review5], $result3);

        $result4 = $this->getReviewDao()->searchClassroomReviews(['rating' => 1], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review5], $result4);

        $result5 = $this->getReviewDao()->searchClassroomReviews(['parentId' => 0], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review1, $review2, $review4, $review5], $result5);
    }

    public function testSearchClassroomReview_withDifferentOrderByAndLimit()
    {
        list($classroom1, $review1) = $this->createClassroomReviews();
        list($classroom1, $review2) = $this->createClassroomReviews($classroom1, ['content' => 'review2', 'userId' => 1000]);
        list($classroom1, $review3) = $this->createClassroomReviews($classroom1, ['content' => 'review3', 'parentId' => $review1['id']]);

        list($classroom2, $review4) = $this->createClassroomReviews(['title' => 'title test classroom'], ['userId' => 1000, 'content' => 'review3']);
        list($classroom2, $review5) = $this->createClassroomReviews($classroom2, ['content' => 'review4', 'rating' => 1]);

        $result1 = $this->getReviewDao()->searchClassroomReviews(['classroomTitle' => 'classroom'], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$review1, $review2, $review3, $review4, $review5], $result1);

        $result2 = $this->getReviewDao()->searchClassroomReviews(['classroomTitle' => 'classroom'], ['id' => 'DESC'], 0, 10);
        $this->assertEquals([$review5, $review4, $review3, $review2, $review1], $result2);

        $result3 = $this->getReviewDao()->searchClassroomReviews(['classroomTitle' => 'classroom'], ['id' => 'ASC'], 0, 3);
        $this->assertEquals([$review1, $review2, $review3], $result3);

        $result4 = $this->getReviewDao()->searchClassroomReviews(['classroomTitle' => 'classroom'], ['id' => 'DESC'], 0, 3);
        $this->assertEquals([$review5, $review4, $review3], $result4);
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

    protected function createReview($fields = [])
    {
        $review = array_merge([
            'userId' => $this->getCurrentUser()->getId(),
            'targetType' => 'goods',
            'targetId' => 1,
            'rating' => 5,
            'content' => 'test content',
            'parentId' => 0,
        ], $fields);

        return $this->getReviewDao()->create($review);
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
     * @return ReviewDao
     */
    protected function getReviewDao()
    {
        return $this->createDao('Review:ReviewDao');
    }
}
