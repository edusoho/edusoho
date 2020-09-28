<?php

namespace Tests\Unit\Goods\Service;

use Biz\BaseTestCase;
use Biz\Goods\Service\RecommendGoodsService;

class RecommendGoodsServiceTest extends BaseTestCase
{
    public function testFindRecommendGoods()
    {
    }

    /**
     * @return RecommendGoodsService
     */
    protected function getRecommendGoodsService()
    {
        return $this->createService('Goods:RecommendGoods');
    }
}
