<?php

namespace Tests\Unit\GoodsMarketing\Service;

use Biz\BaseTestCase;
use Biz\Common\CommonException;
use Biz\GoodsMarketing\Service\MarketingService;

class MarketingServiceTest extends BaseTestCase
{
    public function testGetMeans_whenCreated_thenGot()
    {
        //不存在的means返回null
        $got = $this->getMarketService()->getMeans(1000);
        $this->assertNull($got);

        $means = $this->createMeans();
        $got = $this->getMarketService()->getMeans($means['id']);
        $this->assertEquals($means, $got);
    }

    public function testCreateMeans_whenValidParams_thenCreated()
    {
        $created = $this->getMarketService()->createMeans($this->mockMeans(['fromMeansId' => 10]));
        $got = $this->getMarketService()->getMeans($created['id']);
        $this->assertEquals(10, $created['fromMeansId']);
        $this->assertEquals($created, $got);
    }

    public function testCreateMeans_whenInvalidParams_thenThrowException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionCode(CommonException::ERROR_PARAMETER_MISSING);
        $this->getMarketService()->createMeans([]);
    }

    public function testUpdate()
    {
        $createdMeans = $this->createMeans($mockMeans = $this->mockMeans());
        $this->assertEquals($mockMeans['status'], $createdMeans['status']);
        $updatedMeans = $this->getMarketService()->updateMeans($createdMeans['id'], ['status' => 2]);
        $this->assertEquals(2, $updatedMeans['status']);
    }

    public function testFindValidMeansByTargetTypeAndTargetId()
    {
        $this->createMeans($mockMeans = $this->mockMeans());
        $this->createMeans($mockMeans = $this->mockMeans(['fromMeansId' => 10]));
        $results = $this->getMarketService()->findValidMeansByTargetTypeAndTargetId('goods', 1);
        $this->assertCount(2, $results);
    }

    public function testSearchMeans()
    {
        //无数据情况下返回[]
        $results = $this->getMarketService()->searchMeans(['fromMeansId' => 10], [], 0, 10);
        $this->assertEquals([], $results);

        $this->createMeans($mockMeans = $this->mockMeans());
        $this->createMeans($mockMeans = $this->mockMeans(['fromMeansId' => 10]));
        $results = $this->getMarketService()->searchMeans(['fromMeansId' => 10], [], 0, 10);
        $this->assertCount(1, $results);
    }

    public function testDelete()
    {
        $created = $this->createMeans($mockMeans = $this->mockMeans());
        $this->assertNotEmpty($created);
        $this->getMarketService()->deleteMeans($created['id']);
        $this->assertEmpty($this->getMarketService()->getMeans($created['id']));
    }

    protected function createMeans($means = [])
    {
        $means = $this->mockMeans($means);

        return $this->getMarketService()->createMeans($means);
    }

    protected function mockMeans($means = [])
    {
        $defaultMeans = [
            'type' => 'discount',
            'fromMeansId' => 1,
            'targetType' => 'goods',
            'targetId' => 1,
            'status' => 1,
            'visibleOnGoodsPage' => 1,
        ];

        return array_merge($defaultMeans, $means);
    }

    /**
     * @return MarketingService
     */
    protected function getMarketService()
    {
        return $this->getBiz()->service('GoodsMarketing:MarketingService');
    }
}
