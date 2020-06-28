<?php

namespace Tests\Unit\Review\Service;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseTestCase;
use Biz\Common\CommonException;
use Biz\Goods\GoodsException;
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

    public function testGetByUserIdAndTargetTypeAndTargetId()
    {
        $review = $this->createReview();
        $resultNull = $this->getReviewService()->getByUserIdAndTargetTypeAndTargetId($review['userId'] + 10000, $review['targetType'], $review['targetId']);
        $this->assertNull($resultNull);

        $result = $this->getReviewService()->getByUserIdAndTargetTypeAndTargetId($review['userId'], $review['targetType'], $review['targetId']);
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

    public function testDeleteReviewsByParentId()
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

        $this->getReviewService()->deleteReviewsByParentId($review['id']);

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
        $this->assertEquals($before, $after);

        $this->assertNull($after1);
        $this->assertNull($after2);
        $this->assertNull($after3);
        $this->assertNull($after4);
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
