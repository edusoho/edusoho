<?php

namespace Tests\Unit\Goods\Service;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseTestCase;
use Biz\Common\CommonException;
use Biz\Goods\Service\PurchaseService;

class PurchaseServiceTest extends BaseTestCase
{
    public function testGetVoucher()
    {
        $created = $this->createVoucher();
        $result = $this->getPurchaseService()->getVoucher($created['id']);
        $this->assertEquals($created, $result);
    }

    public function testCreateVoucher()
    {
        $created = $this->createVoucher(['goodsId' => 3, 'specsId' => 10]);
        $this->assertEquals(3, $created['goodsId']);
        $this->assertEquals(10, $created['specsId']);
    }

    public function testCreateVoucher_whenParamsInvalid_thenThrowException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionCode(CommonException::ERROR_PARAMETER_MISSING);
        $this->getPurchaseService()->createVoucher([]);
    }

    public function testUpdateVoucher()
    {
        $created = $this->createVoucher(['goodsId' => 2]);
        $this->assertEquals(2, $created['goodsId']);
        $updated = $this->getPurchaseService()->updateVoucher($created['id'], ['goodsId' => 3]);
        $this->assertEquals(3, $updated['goodsId']);
    }

    public function testDeleteVoucher()
    {
        $created = $this->createVoucher();
        $result = $this->getPurchaseService()->getVoucher($created['id']);
        $this->assertEquals($created, $result);
        $this->getPurchaseService()->deleteVoucher($created['id']);
        $resultAfterDelete = $this->getPurchaseService()->getVoucher($created['id']);
        $this->assertNull($resultAfterDelete);
    }

    public function testCountVouchers()
    {
        $this->createVoucher();
        $this->createVoucher(['goodsId' => 2, 'specsId' => 2]);
        $this->createVoucher(['goodsId' => 3, 'specsId' => 3]);
        $this->createVoucher(['goodsId' => 4, 'specsId' => 4, 'effectiveType' => 'vip']);
        $count = $this->getPurchaseService()->countVouchers(['userId' => 1]);
        $this->assertEquals(4, $count);
        $count1 = $this->getPurchaseService()->countVouchers(['effectiveType' => 'vip']);
        $this->assertEquals(1, $count1);
    }

    public function testSearchVouchers()
    {
        $mocked1 = $this->createVoucher();
        $mocked2 = $this->createVoucher(['goodsId' => 2, 'specsId' => 2]);
        $mocked3 = $this->createVoucher(['goodsId' => 3, 'specsId' => 3]);
        $mocked4 = $this->createVoucher(['goodsId' => 4, 'specsId' => 4, 'effectiveType' => 'vip']);
        $results1 = $this->getPurchaseService()->searchVouchers(['userId' => 1], ['id' => 'DESC'], 0, 10);
        $this->assertCount(4, $results1);
        $this->assertEquals([], array_diff(
            [$mocked1['id'], $mocked2['id'], $mocked3['id'], $mocked4['id']],
            ArrayToolkit::column($results1, 'id')
        ));

        $results2 = $this->getPurchaseService()->searchVouchers(['effectiveType' => 'vip'], [], 0, 10);
        $this->assertCount(1, $results2);
        $this->assertEquals($mocked4, reset($results2));
    }

    public function testFindVouchersByIds()
    {
        $mocked1 = $this->createVoucher();
        $mocked2 = $this->createVoucher(['goodsId' => 2, 'specsId' => 2]);
        $mocked3 = $this->createVoucher(['goodsId' => 3, 'specsId' => 3]);
        $mocked4 = $this->createVoucher(['goodsId' => 4, 'specsId' => 4, 'effectiveType' => 'vip']);
        $results1 = $this->getPurchaseService()->findVouchersByIds([$mocked1['id']]);
        $this->assertEquals($mocked1, reset($results1));
        $results2 = ArrayToolkit::index($this->getPurchaseService()->findVouchersByIds([
            $mocked1['id'],
            $mocked2['id'],
            $mocked3['id'],
            $mocked4['id'],
        ]), 'id');

        $this->assertCount(4, $results2);
        $this->assertEquals([], array_diff(
            ArrayToolkit::column($results2, 'id'),
            [$mocked1['id'], $mocked2['id'], $mocked3['id'], $mocked4['id']]
        ));
        $this->assertEquals($mocked4, $results2[$mocked4['id']]);
    }

    protected function mockVoucher($custom = [])
    {
        return array_merge([
            'specsId' => 1,
            'goodsId' => 1,
            'userId' => 1,
            'effectiveType' => 'forever',
        ], $custom);
    }

    protected function createVoucher($custom = [])
    {
        return $this->getPurchaseService()->createVoucher($this->mockVoucher($custom));
    }

    /**
     * @return PurchaseService
     */
    protected function getPurchaseService()
    {
        return $this->createService('Goods:PurchaseService');
    }
}
