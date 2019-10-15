<?php

namespace Tests\Unit\Coupon\Service;

use Biz\BaseTestCase;

class CouponServiceBatchTest extends BaseTestCase
{
    public function testFindBatchsByIds()
    {
        $batch1 = array(
            'token' => 0,
            'name' => '优惠券',
            'type' => 'minus',
            'generatedNum' => 10,
            'usedNum' => 1,
            'rate' => 0,
            'prefix' => 'couponBatch',
            'digits' => 8,
            'money' => 0,
            'deadlineMode' => 'day',
            'deadline' => 0,
            'fixedDay' => 10,
            'targetType' => 'all',
            'targetId' => 0,
            'description' => 'test',
            'createdTime' => time(),
            'fullDiscountPrice' => 0,
            'unreceivedNum' => 0,
            'codeEnable' => 0,
            'linkEnable' => 0,
            'h5MpsEnable' => 0,
        );
        $batch = $this->getCouponBatchDao()->create($batch1);
        $results = $this->getCouponBatchService()->findBatchsByIds(array($batch['id']));
        $results = array_values($results);
        $this->assertEquals($results[0]['name'], $batch1['name']);
    }

    public function testGetBatch()
    {
        $batch1 = array(
            'token' => 0,
            'name' => '优惠券',
            'type' => 'minus',
            'generatedNum' => 10,
            'usedNum' => 1,
            'rate' => 0,
            'prefix' => 'couponBatch',
            'digits' => 8,
            'money' => 0,
            'deadlineMode' => 'day',
            'deadline' => 0,
            'fixedDay' => 10,
            'targetType' => 'all',
            'targetId' => 0,
            'description' => 'test',
            'createdTime' => time(),
            'fullDiscountPrice' => 0,
            'unreceivedNum' => 0,
            'codeEnable' => 0,
            'linkEnable' => 0,
            'h5MpsEnable' => 0,
        );
        $batch = $this->getCouponBatchDao()->create($batch1);
        $result = $this->getCouponBatchService()->getBatch($batch['id']);
        $this->assertEquals($result['name'], $batch1['name']);
    }

    public function testGetBatchByToken()
    {
        $batch1 = array(
            'token' => 'token',
            'name' => '优惠券',
            'type' => 'minus',
            'generatedNum' => 10,
            'usedNum' => 1,
            'rate' => 0,
            'prefix' => 'couponBatch',
            'digits' => 8,
            'money' => 0,
            'deadlineMode' => 'day',
            'deadline' => 0,
            'fixedDay' => 10,
            'targetType' => 'all',
            'targetId' => 0,
            'description' => 'test',
            'createdTime' => time(),
            'fullDiscountPrice' => 0,
            'unreceivedNum' => 0,
            'codeEnable' => 0,
            'linkEnable' => 0,
            'h5MpsEnable' => 0,
        );
        $this->getCouponBatchDao()->create($batch1);
        $result = $this->getCouponBatchService()->getBatchByToken('token');
        $this->assertEquals($result['name'], $batch1['name']);
    }

    public function testUpdateUnreceivedNumByBatchId()
    {
        $batch1 = array(
            'token' => 'token',
            'name' => '优惠券',
            'type' => 'minus',
            'generatedNum' => 10,
            'usedNum' => 1,
            'rate' => 0,
            'prefix' => 'couponBatch',
            'digits' => 8,
            'money' => 0,
            'deadlineMode' => 'day',
            'deadline' => 0,
            'fixedDay' => 10,
            'targetType' => 'fullDiscount',
            'targetId' => 0,
            'description' => 'test',
            'createdTime' => time(),
            'fullDiscountPrice' => 1,
            'unreceivedNum' => 0,
            'codeEnable' => 0,
            'linkEnable' => 0,
            'h5MpsEnable' => 0,
        );
        $batch = $this->getCouponBatchDao()->create($batch1);
        $this->getCouponBatchService()->createBatchCoupons($batch['id'], 9);
        $this->getCouponBatchService()->updateUnreceivedNumByBatchId($batch['id']);
        $result = $this->getCouponBatchService()->getBatch($batch['id']);
        $this->assertEquals($result['unreceivedNum'], 9);
    }

    public function testGenerateCoupon()
    {
        $batchArray = array(
            'name' => '优惠券',
            'type' => 'minus',
            'rate' => 11,
            'prefix' => 'couponBatch',
            'generatedNum' => 10,
            'digits' => 8,
            'deadlineMode' => 'day',
            'deadline' => 0,
            'fixedDay' => 10,
            'targetType' => 'fullDiscount',
            'targetId' => 1,
            'fullDiscountPrice' => 1,
            'channel' => array(
                'codeEnable' => 1,
                'linkEnable' => 1,
                'h5MpsEnable' => 1,
                ),
             );

        $result = $this->getCouponBatchService()->generateCoupon($batchArray);
        $this->assertEquals($result['name'], $batchArray['name']);
    }

    public function testSearchBatchsCount()
    {
        $batch1 = array(
            'token' => 'token',
            'name' => '优惠券',
            'type' => 'minus',
            'generatedNum' => 10,
            'usedNum' => 1,
            'rate' => 0,
            'prefix' => 'couponBatch',
            'digits' => 8,
            'money' => 0,
            'deadlineMode' => 'day',
            'deadline' => 0,
            'fixedDay' => 10,
            'targetType' => 'all',
            'targetId' => 0,
            'description' => 'test',
            'createdTime' => time(),
            'fullDiscountPrice' => 0,
            'unreceivedNum' => 0,
            'codeEnable' => 0,
            'linkEnable' => 0,
            'h5MpsEnable' => 0,
        );
        $batch = $this->getCouponBatchDao()->create($batch1);
        $result = $this->getCouponBatchService()->searchBatchsCount(array('id' => $batch['id']));
        $this->assertEquals($result, 1);
    }

    public function testSearchBatchs()
    {
        $batch1 = array(
            'token' => 'token',
            'name' => '优惠券',
            'type' => 'minus',
            'generatedNum' => 10,
            'usedNum' => 1,
            'rate' => 0,
            'prefix' => 'couponBatch',
            'digits' => 8,
            'money' => 0,
            'deadlineMode' => 'day',
            'deadline' => 0,
            'fixedDay' => 10,
            'targetType' => 'all',
            'targetId' => 0,
            'description' => 'test',
            'createdTime' => time(),
            'fullDiscountPrice' => 0,
            'unreceivedNum' => 0,
            'codeEnable' => 0,
            'linkEnable' => 0,
            'h5MpsEnable' => 0,
        );
        $batch = $this->getCouponBatchDao()->create($batch1);
        $result = $this->getCouponBatchService()->searchBatchs(array('id' => $batch['id']), array(), 0, 1);
        $result = array_values($result);
        $this->assertEquals($result[0]['name'], '优惠券');
    }

    public function testDeleteBatch()
    {
        $batch1 = array(
            'token' => 'token',
            'name' => '优惠券',
            'type' => 'minus',
            'generatedNum' => 10,
            'usedNum' => 1,
            'rate' => 0,
            'prefix' => 'couponBatch',
            'digits' => 8,
            'money' => 0,
            'deadlineMode' => 'day',
            'deadline' => 0,
            'fixedDay' => 10,
            'targetType' => 'all',
            'targetId' => 0,
            'description' => 'test',
            'createdTime' => time(),
            'fullDiscountPrice' => 0,
            'unreceivedNum' => 0,
            'codeEnable' => 0,
            'linkEnable' => 0,
            'h5MpsEnable' => 0,
        );
        $batch = $this->getCouponBatchDao()->create($batch1);
        $this->getCouponBatchService()->createBatchCoupons($batch['id'], 9);
        $this->getCouponBatchService()->deleteBatch($batch['id']);
        $result = $this->getCouponBatchService()->getBatch($batch['id']);
        $this->assertEmpty($result);
    }

    public function testCheckBatchPrefix()
    {
        $batch1 = array(
            'token' => 'token',
            'name' => '优惠券',
            'type' => 'minus',
            'generatedNum' => 10,
            'usedNum' => 1,
            'rate' => 0,
            'prefix' => 'couponBatch',
            'digits' => 8,
            'money' => 0,
            'deadlineMode' => 'day',
            'deadline' => 0,
            'fixedDay' => 10,
            'targetType' => 'all',
            'targetId' => 0,
            'description' => 'test',
            'createdTime' => time(),
            'fullDiscountPrice' => 0,
            'unreceivedNum' => 0,
            'codeEnable' => 0,
            'linkEnable' => 0,
            'h5MpsEnable' => 0,
        );
        $this->getCouponBatchDao()->create($batch1);
        $result = $this->getCouponBatchService()->checkBatchPrefix('couponBatch');
        $this->assertEmpty($result, false);
    }

    public function testReceiveCoupon()
    {
        $batch1 = array(
            'token' => 'token',
            'name' => '优惠券',
            'type' => 'minus',
            'generatedNum' => 10,
            'usedNum' => 1,
            'rate' => 0,
            'prefix' => 'couponBatch',
            'digits' => 8,
            'money' => 0,
            'deadlineMode' => 'day',
            'deadline' => 0,
            'fixedDay' => 10,
            'targetType' => 'all',
            'targetId' => 0,
            'description' => 'test',
            'createdTime' => time(),
            'fullDiscountPrice' => 0,
            'unreceivedNum' => 0,
            'codeEnable' => 0,
            'linkEnable' => 0,
            'h5MpsEnable' => 0,
        );
        $batch = $this->getCouponBatchDao()->create($batch1);
        $this->getCouponBatchService()->createBatchCoupons($batch['id'], 9);
        $result = $this->getCouponBatchService()->receiveCoupon('token', 1);
        $this->assertEquals($result['code'], 'success');
    }

    public function testUpdateBatch()
    {
        $batch1 = array(
            'token' => 'token',
            'name' => '优惠券',
            'type' => 'minus',
            'generatedNum' => 10,
            'usedNum' => 1,
            'rate' => 0,
            'prefix' => 'couponBatch',
            'digits' => 8,
            'money' => 0,
            'deadlineMode' => 'day',
            'deadline' => 0,
            'fixedDay' => 10,
            'targetType' => 'all',
            'targetId' => 0,
            'description' => 'test',
            'createdTime' => time(),
            'fullDiscountPrice' => 0,
            'unreceivedNum' => 0,
            'codeEnable' => 0,
            'linkEnable' => 0,
            'h5MpsEnable' => 0,
        );
        $batch = $this->getCouponBatchDao()->create($batch1);
        $result = $this->getCouponBatchService()->updateBatch($batch['id'], array('name' => '优惠券x'));

        $this->assertEquals($result['name'], '优惠券x');
    }

    public function testSearchH5MpsBatches()
    {
        $batch1 = array(
            'token' => 'token',
            'name' => '优惠券',
            'type' => 'minus',
            'generatedNum' => 10,
            'usedNum' => 1,
            'rate' => 0,
            'prefix' => 'couponBatch',
            'digits' => 8,
            'money' => 0,
            'deadlineMode' => 'day',
            'deadline' => 0,
            'fixedDay' => 10,
            'targetType' => 'course',
            'targetId' => 1,
            'description' => 'test',
            'createdTime' => time(),
            'fullDiscountPrice' => 0,
            'unreceivedNum' => 0,
            'codeEnable' => 1,
            'linkEnable' => 1,
            'h5MpsEnable' => 1,
        );

        $batch = $this->getCouponBatchDao()->create($batch1);
        $this->getCouponBatchService()->receiveCoupon('token', $this->getCurrentUser()->getId());
        $result = $this->getCouponBatchService()->searchH5MpsBatches(array('targetType' => 'course', 'targetId' => 1), 0, 9);
        $this->assertEmpty($result);
    }

    public function testCountH5MpsBatches()
    {
        $batch1 = array(
            'id' => 1,
            'token' => 'token',
            'name' => '优惠券',
            'type' => 'minus',
            'generatedNum' => 10,
            'usedNum' => 1,
            'rate' => 0,
            'prefix' => 'couponBatch',
            'digits' => 8,
            'money' => 0,
            'deadlineMode' => 'day',
            'deadline' => 0,
            'fixedDay' => 10,
            'targetType' => 'course',
            'targetId' => 1,
            'description' => 'test',
            'createdTime' => time(),
            'fullDiscountPrice' => 0,
            'unreceivedNum' => 0,
            'codeEnable' => 1,
            'linkEnable' => 1,
            'h5MpsEnable' => 1,
        );

        $batch = $this->getCouponBatchDao()->create($batch1);
        $this->getCouponBatchService()->receiveCoupon('token', $this->getCurrentUser()->getId());
        $result = $this->getCouponBatchService()->countH5MpsBatches(array('targetType' => 'course', 'targetId' => 1));

        $this->assertEmpty($result);
    }

    public function testFillUserCurrentCouponByBatches()
    {
        $batch1 = array(
            'id' => 1,
            'token' => 'token',
            'name' => '优惠券',
            'type' => 'minus',
            'generatedNum' => 10,
            'usedNum' => 1,
            'rate' => 0,
            'prefix' => 'couponBatch',
            'digits' => 8,
            'money' => 0,
            'deadlineMode' => 'day',
            'deadline' => 0,
            'fixedDay' => 10,
            'targetType' => 'course',
            'targetId' => 1,
            'description' => 'test',
            'createdTime' => time(),
            'fullDiscountPrice' => 0,
            'unreceivedNum' => 0,
            'codeEnable' => 1,
            'linkEnable' => 1,
            'h5MpsEnable' => 1,
        );
        $batch = $this->getCouponBatchDao()->create($batch1);
        $this->mockBiz('Coupon:CouponService', array(
            array(
                'functionName' => 'searchCoupons',
                'returnValue' => array('1' => array('id' => 1, 'batchId' => 1, 'status' => 'receive')),
            ),
            array(
                'functionName' => 'searchCouponsCount',
                'returnValue' => 1,
            ),
        ));
        $result = $this->getCouponBatchService()->fillUserCurrentCouponByBatches(array($batch));
        $result = array_values($result);
        $this->assertEquals($result[0]['name'], $batch['name']);
    }

    public function testGetCouponBatchContent()
    {
        $batch1 = array(
            'id' => 1,
            'token' => 'token',
            'name' => '优惠券',
            'type' => 'minus',
            'generatedNum' => 10,
            'usedNum' => 1,
            'rate' => 0,
            'prefix' => 'couponBatch',
            'digits' => 8,
            'money' => 0,
            'deadlineMode' => 'day',
            'deadline' => 0,
            'fixedDay' => 10,
            'targetType' => 'course',
            'targetId' => 1,
            'targetIds' => array(1, 2),
            'description' => 'test',
            'createdTime' => time(),
            'fullDiscountPrice' => 0,
            'unreceivedNum' => 0,
            'codeEnable' => 1,
            'linkEnable' => 1,
            'h5MpsEnable' => 1,
        );
        $batch = $this->getCouponBatchDao()->create($batch1);
        $batch1['id'] = 2;
        $batch1['targetId'] = 0;
        $batch2 = $this->getCouponBatchDao()->create($batch1);
        $result1 = $this->getCouponBatchService()->getCouponBatchContent($batch['id']);
        $result2 = $this->getCouponBatchService()->getCouponBatchContent($batch2['id']);

        $this->assertEquals($result1, 'multi');
        $this->assertEquals($result2, '全部课程');
    }

    public function testGetCouponBatchTargetDetail()
    {
        $batch1 = array(
            'id' => 1,
            'token' => 'token',
            'name' => '优惠券',
            'type' => 'minus',
            'generatedNum' => 10,
            'usedNum' => 1,
            'rate' => 0,
            'prefix' => 'couponBatch',
            'digits' => 8,
            'money' => 0,
            'deadlineMode' => 'day',
            'deadline' => 0,
            'fixedDay' => 10,
            'targetType' => 'course',
            'targetId' => 1,
            'targetIds' => array(1, 2),
            'description' => 'test',
            'createdTime' => time(),
            'fullDiscountPrice' => 0,
            'unreceivedNum' => 0,
            'codeEnable' => 1,
            'linkEnable' => 1,
            'h5MpsEnable' => 1,
        );
        $batch = $this->getCouponBatchDao()->create($batch1);
        $batch1['id'] = 2;
        $batch1['targetId'] = 0;
        $batch2 = $this->getCouponBatchDao()->create($batch1);
        $result1 = $this->getCouponBatchService()->getCouponBatchTargetDetail($batch['id']);
        $result2 = $this->getCouponBatchService()->getCouponBatchTargetDetail($batch2['id']);

        $this->assertEquals($result1['numType'], 'multi');
        $this->assertEquals($result2['numType'], 'all');
    }

    /**
     * @return \Biz\Coupon\Dao\CouponBatchDao
     */
    protected function getCouponBatchDao()
    {
        return $this->createDao('Coupon:CouponBatchDao');
    }

    /**
     * @return \Biz\Coupon\Dao\CouponDao
     */
    protected function getCouponDao()
    {
        return $this->createDao('Coupon:CouponDao');
    }

    /**
     * @return \Biz\Coupon\Service\Impl\CouponBatchServiceImpl
     */
    private function getCouponBatchService()
    {
        return $this->createService('Coupon:CouponBatchService');
    }
}
