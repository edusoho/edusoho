<?php

namespace Tests\Unit\RefererLog\Service;

use Biz\BaseTestCase;
use Biz\RefererLog\Service\OrderRefererLogService;

class OrderRefererLogServiceTest extends BaseTestCase
{
    public function testGetOrderRefererLog()
    {
        $fields = array(
            'refererLogId' => 1,
            'orderId' => 1,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'course',
            'targetId' => 1,
            'createdTime' => time(),
        );

        $orderRerfererLog = $this->getOrderRefererLogService()->addOrderRefererLog($fields);

        $log = $this->getOrderRefererLogService()->getOrderRefererLog($orderRerfererLog['id']);

        $this->assertEquals($fields['targetType'], $log['targetType']);
        $this->assertEquals($fields['orderId'], $log['orderId']);
    }

    public function testAddOrderRefererLog()
    {
        $fields = array(
            'refererLogId' => 1,
            'orderId' => 1,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'course',
            'targetId' => 1,
            'createdTime' => time(),
        );

        $orderRerfererLog = $this->getOrderRefererLogService()->addOrderRefererLog($fields);

        $this->assertEquals($fields['targetType'], $orderRerfererLog['targetType']);
        $this->assertEquals($fields['orderId'], $orderRerfererLog['orderId']);
    }

    public function testUpdateOrderRefererLog()
    {
        $orderRerfererLog = $this->_createOrderRerfererLog();

        $updateFields = array(
            'createdUserId' => 5,
        );
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
        $fields1 = array(
            'refererLogId' => 1,
            'orderId' => 1,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'course',
            'targetId' => 1,
            'createdTime' => time(),
        );
        $log1 = $this->getOrderRefererLogService()->addOrderRefererLog($fields1);

        $fields2 = array(
            'refererLogId' => 1,
            'orderId' => 2,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'openCourse',
            'targetId' => 1,
            'createdTime' => time(),
        );
        $log2 = $this->getOrderRefererLogService()->addOrderRefererLog($fields2);

        $conditions = array(
            'targetType' => 'openCourse',
        );
        $logs = $this->getOrderRefererLogService()->searchOrderRefererLogs($conditions, array('createdTime' => 'DESC'), 0, 1);

        $this->assertEquals(1, count($logs));
        $this->assertEquals($log2['orderId'], $logs[0]['orderId']);
    }

    public function testSearchOrderRefererLogCount()
    {
        $fields1 = array(
            'refererLogId' => 1,
            'orderId' => 1,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'course',
            'targetId' => 1,
            'createdTime' => time(),
        );
        $log1 = $this->getOrderRefererLogService()->addOrderRefererLog($fields1);

        $fields2 = array(
            'refererLogId' => 1,
            'orderId' => 2,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'openCourse',
            'targetId' => 1,
            'createdTime' => time(),
        );
        $log2 = $this->getOrderRefererLogService()->addOrderRefererLog($fields2);

        $conditions = array(
            'targetType' => 'openCourse',
        );

        $logCount = $this->getOrderRefererLogService()->searchOrderRefererLogCount($conditions);

        $this->assertEquals(1, $logCount);
    }

    public function testSearchDistinctOrderRefererLogCount()
    {
        $fields1 = array(
            'refererLogId' => 1,
            'orderId' => 1,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'course',
            'targetId' => 1,
            'createdTime' => time(),
        );
        $log1 = $this->getOrderRefererLogService()->addOrderRefererLog($fields1);

        $fields2 = array(
            'refererLogId' => 2,
            'orderId' => 1,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'course',
            'targetId' => 1,
            'createdTime' => time(),
        );
        $log2 = $this->getOrderRefererLogService()->addOrderRefererLog($fields2);

        $conditions = array(
            'targetId' => 1,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
        );

        $count = $this->getOrderRefererLogService()->searchDistinctOrderRefererLogCount($conditions, 'orderId');

        $this->assertEquals(1, $count);
    }

    private function _createOrderRerfererLog()
    {
        $fields = array(
            'refererLogId' => 1,
            'orderId' => 1,
            'sourceTargetType' => 'openCourse',
            'sourceTargetId' => 1,
            'targetType' => 'course',
            'targetId' => 1,
            'createdTime' => time(),
        );

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
