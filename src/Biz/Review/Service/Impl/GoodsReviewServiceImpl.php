<?php

namespace Biz\Review\Service\Impl;

use Biz\Goods\GoodsException;
use Biz\Goods\Service\GoodsService;
use Biz\Goods\Service\PurchaseService;
use Biz\Review\ReviewException;
use Biz\Review\Service\ReviewService;

class GoodsReviewServiceImpl extends ReviewServiceImpl implements ReviewService
{
    public function tryCreateReview($review)
    {
        $goods = $this->getGoodsService()->getGoods($review['targetId']);
        if (empty($goods)) {
            $this->createNewException(GoodsException::GOODS_NOT_FOUND());
        }

        $purchaseCount = $this->getGoodsPurchaseService()->countVouchers([
            'userId' => $review['userId'],
            'goodsId' => $goods['id'],
        ]);

        if (!$purchaseCount) {
            $this->createNewException(ReviewException::FORBIDDEN_CREATE_REVIEW());
        }

        return $review;
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }

    /**
     * @return PurchaseService
     */
    protected function getGoodsPurchaseService()
    {
        return $this->createService('Goods:PurchaseService');
    }
}
