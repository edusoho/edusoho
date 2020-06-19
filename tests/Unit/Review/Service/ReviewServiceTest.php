<?php

namespace Tests\Unit\Review\Service;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseTestCase;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Goods\GoodsException;
use Biz\Review\Dao\ReviewDao;
use Biz\Review\ReviewException;
use Biz\Review\Service\ReviewService;

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

    public function testTryCreateReview_whenTargetTypeNotExpected_thenThrowException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionMessage('exception.common_parameter_error');

        $this->getReviewService()->tryCreateReview($this->mockDefaultReview(['targetType' => 'testType']));
    }

    public function testTryCreateGoodsReview_whenGoodsNotExist_thenThrowException()
    {
        $this->expectException(GoodsException::class);
        $this->expectExceptionMessage('exception.goods.not_found');

        $this->getReviewService()->tryCreateReview($this->mockDefaultReview());
    }

    public function testTryCreateGoodsReview_whenProductPurchaseNotExist_thenThrowException()
    {
        $this->expectException(ReviewException::class);
        $this->expectExceptionMessage('exception.review.forbidden_create_review');

        $mockedGoodsService = $this->mockBiz('Goods:GoodsService', [
            [
                'functionName' => 'getGoods',
                'returnValue' => ['id' => 1],
            ],
        ]);

        $this->getReviewService()->tryCreateReview($this->mockDefaultReview());
        $mockedGoodsService->shouldHaveReceived('getGoods')->times(1);
    }

    public function testTryCreateGoodsReview()
    {
        $expected = $this->mockDefaultReview();

        $mockedGoodsService = $this->mockBiz('Goods:GoodsService', [
            [
                'functionName' => 'getGoods',
                'returnValue' => ['id' => 1],
            ],
        ]);

        $mockedServices = $this->mockBiz('Goods:PurchaseService', [
            [
                'functionName' => 'countVouchers',
                'returnValue' => 123,
            ],
        ]);

        $result = $this->getReviewService()->tryCreateReview($expected);

        $mockedGoodsService->shouldHaveReceived('getGoods')->times(1);
        $mockedServices->shouldHaveReceived('countVouchers')->times(1);
        $this->assertEquals($expected, $result);
    }

    public function testTryCreateCourseReview_whenCourseNotExist_thenThrowException()
    {
        $this->expectException(CourseException::class);
        $this->expectExceptionMessage('exception.course.not_found');

        $this->getReviewService()->tryCreateReview($this->mockDefaultReview(['targetType' => 'course']));
    }

    public function testTryCreateCourseReview_whenCourseParentIdInvalid_thenThrowException()
    {
        $this->expectException(ReviewException::class);
        $this->expectExceptionMessage('exception.review.forbidden_create_review');

        $mockedCourseService = $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'getCourse',
                'returnValue' => ['id' => 1, 'parentId' => 0],
            ],
        ]);

        $this->getReviewService()->tryCreateReview($this->mockDefaultReview(['targetType' => 'course']));
        $mockedCourseService->shouldHaveReceived('getCourse')->times(1);
    }

    public function testTryCreateCourseReview_whenNotCourseMember_thenThrowException()
    {
        $this->expectException(ReviewException::class);
        $this->expectExceptionMessage('exception.review.forbidden_create_review');

        $mockedCourseService = $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'getCourse',
                'returnValue' => ['id' => 1, 'parentId' => 1],
            ],
        ]);

        $this->getReviewService()->tryCreateReview($this->mockDefaultReview(['targetType' => 'course']));
        $mockedCourseService->shouldHaveReceived('getCourse')->times(1);
    }

    public function testTryCreateCourseReview()
    {
        $mockedCourseService = $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'getCourse',
                'returnValue' => ['id' => 1, 'parentId' => 1],
            ],
        ]);

        $mockedMember = $this->mockBiz('Course:MemberService', [
            [
                'functionName' => 'getCourseMember',
                'returnValue' => ['id' => 1],
            ],
        ]);

        $expected = $this->mockDefaultReview(['targetType' => 'course']);
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
        $this->expectExceptionMessage('exception.review.rating_no_more_than_5');
        $review = $this->mockDefaultReview();
        $review['rating'] = 100;
        $this->getReviewService()->createReview($review);
    }

    public function testCreateReview()
    {
        $mockedGoodsService = $this->mockBiz('Goods:GoodsService', [
            [
                'functionName' => 'getGoods',
                'returnValue' => ['id' => 1],
            ],
        ]);

        $mockedServices = $this->mockBiz('Goods:PurchaseService', [
            [
                'functionName' => 'countVouchers',
                'returnValue' => 123,
            ],
        ]);

        $result = $this->getReviewService()->createReview($this->mockDefaultReview());

        $expected = $this->getReviewService()->getReview(1);
        $this->assertEquals($expected, $result);
        $mockedGoodsService->shouldHaveReceived('getGoods')->times(1);
        $mockedServices->shouldHaveReceived('countVouchers')->times(1);
    }

    public function testGetByUserIdAndTargetIdAndTargetType()
    {
        $review = $this->createReview();
        $resultNull = $this->getReviewService()->getByUserIdAndTargetIdAndTargetType($review['userId'] + 10000, $review['targetType'], $review['targetId']);
        $this->assertNull($resultNull);

        $result = $this->getReviewService()->getByUserIdAndTargetIdAndTargetType($review['userId'], $review['targetType'], $review['targetId']);
        $this->assertEquals($review, $result);
    }

    public function testUpdateReview()
    {
        $review = $this->createReview();

        $before = $this->getReviewService()->getReview($review['id']);
        $result = $this->getReviewService()->updateReview($review['id'], ['content' => 'test update', 'rating' => 5]);

        $this->assertNotEquals($before, $result);
        $this->assertEquals($before['rating'], $result['rating']);
        $this->assertEquals($review['content'], $before['content']);
        $this->assertEquals('test update', $result['content']);
    }

    public function testDeleteReview()
    {
        $review = $this->createReview();
        $before = $this->getReviewService()->getReview($review['id']);

        $this->getReviewService()->deleteReview($review['id']);

        $after = $this->getReviewService()->getReview($review['id']);

        $this->assertEquals($review, $before);
        $this->assertNull($after);
    }

    public function testCountReview()
    {
        $review1 = $this->createReview();
        $review2 = $this->createReview(['userId' => 23]);
        $review3 = $this->createReview(['targetType' => 'course']);
        $review4 = $this->createReview(['targetId' => '4']);
        $review5 = $this->createReview(['targetType' => 'course']);

        $count1 = $this->getReviewService()->countReview(['targetType' => 'course']);
        $this->assertEquals(2, $count1);

        $count2 = $this->getReviewService()->countReview(['targetId' => 1]);
        $this->assertEquals(4, $count2);

        $count3 = $this->getReviewService()->countReview(['userId' => 23]);
        $this->assertEquals(1, $count3);

        $count4 = $this->getReviewService()->countReview(['targetType' => 'goods', 'targetId' => 1]);
        $this->assertEquals(2, $count4);
    }

    public function testSearchReview_withDifferentConditions()
    {
        $review1 = $this->createReview();
        $review2 = $this->createReview(['userId' => 23]);
        $review3 = $this->createReview(['targetType' => 'course']);
        $review4 = $this->createReview(['targetId' => '4']);
        $review5 = $this->createReview(['targetType' => 'course']);

        $result1 = $this->getReviewService()->searchReview(['userId' => 23], [], 0, 10);
        $this->assertEquals([$review2], $result1);

        $expected2 = ArrayToolkit::index([$review3, $review5], 'id');
        $result2 = $this->getReviewService()->searchReview(['targetType' => 'course'], [], 0, 10);

        $result2 = ArrayToolkit::index($result2, 'id');
        $this->assertEquals($expected2, $result2);

        $expected3 = ArrayToolkit::index([$review1, $review2, $review3, $review5], 'id');
        $result3 = $this->getReviewService()->searchReview(['targetId' => 1], [], 0, 10);
        $result3 = ArrayToolkit::index($result3, 'id');

        $this->assertEquals($expected3, $result3);

        $expected4 = ArrayToolkit::index([$review1, $review2], 'id');
        $result4 = $this->getReviewService()->searchReview(['targetType' => 'goods', 'targetId' => 1], [], 0, 10);
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

        $result1 = $this->getReviewService()->searchReview(['userId' => 23], ['id' => 'desc'], 0, 10);
        $this->assertEquals([$review2], $result1);

        $result2 = $this->getReviewService()->searchReview(['targetType' => 'course'], ['id' => 'desc'], 0, 10);
        $this->assertEquals([$review5, $review3], $result2);

        $result3 = $this->getReviewService()->searchReview(['targetId' => 1], ['id' => 'desc'], 0, 10);
        $this->assertEquals([$review5, $review3, $review2, $review1], $result3);

        $result4 = $this->getReviewService()->searchReview(['targetType' => 'goods', 'targetId' => 1], ['id' => 'desc'], 0, 10);
        $this->assertEquals([$review2, $review1], $result4);

        $result4 = $this->getReviewService()->searchReview(['targetType' => 'goods', 'targetId' => 1], ['id' => 'desc'], 0, 1);
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

        $result1 = $this->getReviewService()->searchReview(['userId' => 23], ['id' => 'desc'], 0, 10, ['targetType', 'targetId', 'userId']);
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
        $result2 = $this->getReviewService()->searchReview(['targetType' => 'course'], ['id' => 'desc'], 0, 10, ['targetType', 'targetId', 'content']);
        $this->assertEquals($expected2, $result2);
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
            'targetId' => 1,
            'rating' => 5,
            'content' => 'test content',
            'parentId' => 0,
        ], $fields);
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
