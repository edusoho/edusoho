<?php

namespace Tests\Unit\Goods\Service;

use Biz\BaseTestCase;
use Biz\Goods\Dao\GoodsDao;
use Biz\Goods\Service\RecommendGoodsService;

class RecommendGoodsServiceTest extends BaseTestCase
{
    public function testFindRecommendGoodsByGoods_whenSettingRecommendRuleHot()
    {
        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'withParams' => ['goods_setting', []],
                'returnValue' => ['recommend_rule' => 'hot'],
            ],
        ]);

        $goods1 = $this->createGoods(['hotSeq' => 10, 'status' => 'published']);
        $goods2 = $this->createGoods(['hotSeq' => 20, 'status' => 'published']);

        $result = $this->getRecommendGoodsService()->findRecommendedGoodsByGoods([
            'type' => 'course',
            'id' => 10,
        ]);

        self::assertEquals($goods2, $result[0]);
        self::assertEquals($goods1, $result[1]);
    }

    public function testFindRecommendGoodsByGoods_whenSettingRecommendRuleLatest()
    {
        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'withParams' => ['goods_setting', []],
                'returnValue' => ['recommend_rule' => 'latest'],
            ],
        ]);
        $goods1 = $this->createGoods(['status' => 'published', 'publishedTime' => 1000]);
        $goods2 = $this->createGoods(['status' => 'published', 'publishedTime' => 2000]);
        $results = $this->getRecommendGoodsService()->findRecommendedGoodsByGoods([
            'type' => 'course',
            'id' => 100,
        ]);

        self::assertEquals($goods2, $results[0]);
        self::assertEquals($goods1, $results[1]);
    }

    public function testFindRecommendGoodsByGoods_whenSettingRecommendRuleTag()
    {
        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'withParams' => ['goods_setting', []],
                'returnValue' => ['recommend_rule' => 'label'],
            ],
        ]);
        $this->createGoods(['status' => 'published', 'publishedTime' => 1000]);
        $results = $this->getRecommendGoodsService()->findRecommendedGoodsByGoods([
            'type' => 'course',
            'id' => 100,
            'productId' => 100,
        ]);

        self::assertEmpty($results);
    }

    protected function createGoods($goods = [])
    {
        $default = [
            'productId' => 1,
            'type' => 'course',
            'title' => 'testTitle',
            'images' => [],
        ];

        $goods = array_merge($default, $goods);

        return $this->getGoodsDao()->create($goods);
    }

    /**
     * @return RecommendGoodsService
     */
    protected function getRecommendGoodsService()
    {
        return $this->createService('Goods:RecommendGoodsService');
    }

    /**
     * @return GoodsDao
     */
    protected function getGoodsDao()
    {
        return $this->createDao('Goods:GoodsDao');
    }
}
