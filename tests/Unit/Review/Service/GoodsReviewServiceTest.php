<?php

namespace Tests\Unit\Review\Service;

use Biz\BaseTestCase;
use Biz\Goods\GoodsException;
use Biz\Review\Dao\ReviewDao;
use Biz\Review\ReviewException;
use Biz\Review\Service\GoodsReviewServiceImpl;

class GoodsReviewServiceTest extends BaseTestCase
{
    public function testTryCreateReview_whenGoodsEmpty_thenThrowException()
    {
        $this->mockBiz('Goods:GoodsService', [
            [
                'functionName' => 'getGoods',
                'returnValue' => [],
            ],
        ]);

        $this->expectException(GoodsException::class);
        $this->expectExceptionMessage('exception.goods.not_found');

        $this->getGoodReviewService()->tryCreateReview(['targetId' => 1]);
    }

    public function testTryCreateReview_whenVouchersEmpty_thenThrowException()
    {
        $this->mockBiz('Goods:GoodsService', [
            [
                'functionName' => 'getGoods',
                'returnValue' => ['id' => 1],
            ],
        ]);

        $this->mockBiz('Goods:PurchaseService', [
            [
                'functionName' => 'countVouchers',
                'returnValue' => null,
            ],
        ]);

        $this->expectException(ReviewException::class);
        $this->expectExceptionMessage('exception.review.forbidden_create_review');

        $this->getGoodReviewService()->tryCreateReview(['targetId' => 1, 'userId' => 1]);
    }

    public function testTryCreateReview()
    {
        $review = ['targetId' => 1, 'userId' => 1];

        $this->mockBiz('Goods:GoodsService', [
            [
                'functionName' => 'getGoods',
                'returnValue' => ['id' => 1],
            ],
        ]);

        $this->mockBiz('Goods:PurchaseService', [
            [
                'functionName' => 'countVouchers',
                'returnValue' => 1,
            ],
        ]);

        $result = $this->getGoodReviewService()->tryCreateReview($review);
        $this->assertEquals($review, $result);
    }

    /**
     * @return GoodsReviewServiceImpl
     */
    protected function getGoodReviewService()
    {
        return $this->createService('Review:GoodsReviewService');
    }

    /**
     * @return ReviewDao
     */
    protected function getReviewDao()
    {
        return $this->createDao('Review:ReviewDao');
    }
}
