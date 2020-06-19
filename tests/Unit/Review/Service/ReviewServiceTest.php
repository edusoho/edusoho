<?php

namespace Tests\Unit\Review\Service;

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
