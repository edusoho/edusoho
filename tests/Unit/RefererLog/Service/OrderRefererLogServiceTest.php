<?php

namespace Tests\Unit\RefererLog\Service;

use Biz\BaseTestCase;
use Biz\RefererLog\Service\OrderRefererLogService;

class OrderRefererLogServiceTest extends BaseTestCase
{
    public function testGetOrderRefererLog()
    {
        $fields = [
            'refererLogId' => 1,
            'orderId' => 1,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'course',
            'targetId' => 1,
            'createdTime' => time(),
        ];

        $orderRerfererLog = $this->getOrderRefererLogService()->addOrderRefererLog($fields);

        $log = $this->getOrderRefererLogService()->getOrderRefererLog($orderRerfererLog['id']);

        $this->assertEquals($fields['targetType'], $log['targetType']);
        $this->assertEquals($fields['orderId'], $log['orderId']);
    }

    public function testAddOrderRefererLog()
    {
        $fields = [
            'refererLogId' => 1,
            'orderId' => 1,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'course',
            'targetId' => 1,
            'createdTime' => time(),
        ];

        $orderRerfererLog = $this->getOrderRefererLogService()->addOrderRefererLog($fields);

        $this->assertEquals($fields['targetType'], $orderRerfererLog['targetType']);
        $this->assertEquals($fields['orderId'], $orderRerfererLog['orderId']);
    }

    public function testUpdateOrderRefererLog()
    {
        $orderRerfererLog = $this->_createOrderRerfererLog();

        $updateFields = [
            'createdUserId' => 5,
        ];
        $updateLog = $this->getOrderRefererLogService()->updateOrderRefererLog($orderRerfererLog['id'], $updateFields);

        $this->assertEquals($updateFields['createdUserId'], $updateLog['createdUserId']);
    }

    public function testDeleteOrderRefererLog()
    {
        $orderRerfererLog = $this->_createOrderRerfererLog();

        $this->getOrderRefererLogService()->deleteOrderRefererLog($orderRerfererLog['id']);
        $deleteLog = $this->getOrderRefererLogService()->getOrderRefererLog($orderRerfererLog['id']);

        $this->assertNull($deleteLog);
    }

    public function testSearchOrderRefererLogs()
    {
        $fields1 = [
            'refererLogId' => 1,
            'orderId' => 1,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'course',
            'targetId' => 1,
            'createdTime' => time(),
        ];
        $log1 = $this->getOrderRefererLogService()->addOrderRefererLog($fields1);

        $fields2 = [
            'refererLogId' => 1,
            'orderId' => 2,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'openCourse',
            'targetId' => 1,
            'createdTime' => time(),
        ];
        $log2 = $this->getOrderRefererLogService()->addOrderRefererLog($fields2);

        $conditions = [
            'targetType' => 'openCourse',
        ];
        $logs = $this->getOrderRefererLogService()->searchOrderRefererLogs($conditions, ['buyNum' => 'DESC'], 0, 1);

        $this->assertEquals(1, count($logs));
        $this->assertEquals(1, $logs[0]['buyNum']);
    }

    public function testSearchOrderRefererLogCount()
    {
        $fields1 = [
            'refererLogId' => 1,
            'orderId' => 1,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'course',
            'targetId' => 1,
            'createdTime' => time(),
        ];
        $log1 = $this->getOrderRefererLogService()->addOrderRefererLog($fields1);

        $fields2 = [
            'refererLogId' => 1,
            'orderId' => 2,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'openCourse',
            'targetId' => 1,
            'createdTime' => time(),
        ];
        $log2 = $this->getOrderRefererLogService()->addOrderRefererLog($fields2);

        $conditions = [
            'targetType' => 'openCourse',
        ];

        $logCount = $this->getOrderRefererLogService()->searchOrderRefererLogCount($conditions);

        $this->assertEquals(1, $logCount);
    }

    public function testSearchDistinctOrderRefererLogCount()
    {
        $fields1 = [
            'refererLogId' => 1,
            'orderId' => 1,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'course',
            'targetId' => 1,
            'createdTime' => time(),
        ];
        $log1 = $this->getOrderRefererLogService()->addOrderRefererLog($fields1);

        $fields2 = [
            'refererLogId' => 2,
            'orderId' => 1,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'course',
            'targetId' => 1,
            'createdTime' => time(),
        ];
        $log2 = $this->getOrderRefererLogService()->addOrderRefererLog($fields2);

        $conditions = [
            'targetId' => 1,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
        ];

        $count = $this->getOrderRefererLogService()->searchDistinctOrderRefererLogCount($conditions, 'orderId');

        $this->assertEquals(1, $count);
    }

    private function _createOrderRerfererLog()
    {
        $fields = [
            'refererLogId' => 1,
            'orderId' => 1,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'course',
            'targetId' => 1,
            'createdTime' => time(),
        ];

        return $this->getOrderRefererLogService()->addOrderRefererLog($fields);
    }

    /**
     * @return OrderRefererLogService
     */
    protected function getOrderRefererLogService()
    {
        return $this->createService('RefererLog:OrderRefererLogService');
    }
}
