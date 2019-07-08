<?php

namespace Tests\Unit\Notification\Service;

use Biz\BaseTestCase;
use Biz\Notification\Service\NotificationService;

class NotificationServiceTest extends BaseTestCase
{
    public function testCountBatchesWithSearchAll()
    {
        $this->createBatch();
        $count = $this->getNotificationService()->countBatches(array());
        $this->assertEquals(1, $count);
    }

    public function testCountBatchesConditions()
    {
        $batch = $this->createBatch();
        $count = $this->getNotificationService()->countBatches(array('id' => $batch['id']));
        $this->assertEquals(1, $count);

        $count = $this->getNotificationService()->countBatches(array('id' => $batch['id'] + 1));
        $this->assertEquals(0, $count);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testCreateBatchException()
    {
        $batch = array('eventId' => 1);
        $this->getNotificationService()->createBatch($batch);
    }

    public function testCreateBatch()
    {
        $batch = $this->createBatch();
        $this->assertEquals('test12345', $batch['sn']);
        $this->assertEquals(1, $batch['eventId']);
        $getBatch = $this->getNotificationService()->getBatch($batch['id']);
        $this->assertEquals($batch, $getBatch);
    }

    public function testGetBatch()
    {
        $expected = $this->createBatch();
        $notification = $this->getNotificationService()->getBatch($expected['id']);
        $this->assertArraySternEquals($expected, $notification);
    }

    public function testSearchBatchesOrderBy()
    {
        $notification1 = $this->createBatch();
        sleep(1);
        $fields = array(
            'sn' => 'testNotify',
            'eventId' => 2,
            'strategyId' => 2,
        );
        $notification2 = $this->createBatch($fields);
        $notifications = $this->getNotificationService()->searchBatches(array(), array('createdTime' => 'DESC'), 0, 100);
        $this->assertArraySternEquals(array($notification2, $notification1), $notifications);

        $notifications = $this->getNotificationService()->searchBatches(array(), array('id' => 'ASC'), 0, 100);
        $this->assertArraySternEquals(array($notification1, $notification2), $notifications);

        $notifications = $this->getNotificationService()->searchBatches(array(), array('updatedTime' => 'ASC'), 0, 100);
        $this->assertArraySternEquals(array($notification1, $notification2), $notifications);
    }

    public function testSearchBatchesConditions()
    {
        $notification1 = $this->createBatch();
        sleep(1);
        $fields = array(
            'sn' => 'testNotify',
            'eventId' => 2,
            'strategyId' => 2,
        );
        $notification2 = $this->createBatch($fields);
        $notifications = $this->getNotificationService()->searchBatches(array('id' => $notification1['id']), array(), 0, 100);
        $this->assertArraySternEquals(array($notification1), $notifications);

        $notifications = $this->getNotificationService()->searchBatches(array('eventId' => $notification1['eventId']), array(), 0, 100);
        $this->assertArraySternEquals(array($notification1), $notifications);

        $notifications = $this->getNotificationService()->searchBatches(array('sn' => $notification2['sn']), array(), 0, 100);
        $this->assertArraySternEquals(array($notification2), $notifications);

        $notifications = $this->getNotificationService()->searchBatches(array('status' => $notification2['status']), array('id' => 'ASC'), 0, 100);
        $this->assertArraySternEquals(array($notification1, $notification2), $notifications);

        $notifications = $this->getNotificationService()->searchBatches(array('strategyId' => $notification2['strategyId']), array(), 0, 100);
        $this->assertArraySternEquals(array($notification2), $notifications);
    }

    public function testFindEventsByIds()
    {
        $event = $this->createEvent();

        $result = $this->getNotificationService()->findEventsByIds(array($event['id']));

        $this->assertCount(1, $result);
        $this->assertArraySternEquals($event, $result[0]);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testCreateEventException()
    {
        $event = array('title' => 'eventTitle');
        $this->getNotificationService()->createEvent($event);
    }

    public function testCreateEvent()
    {
        $event = $this->createEvent();
        $this->assertEquals('test Events', $event['title']);
    }

    public function testGetEvent()
    {
        $expected = $this->createEvent();
        $event = $this->getNotificationService()->getEvent($expected['id']);

        $this->assertArraySternEquals($expected, $event);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testCreateStrategyException()
    {
        $strategy = array('eventId' => 1);
        $this->getNotificationService()->createStrategy($strategy);
    }

    public function testCreateStrategy()
    {
        $strategy = $this->createStrategy();
        $this->assertEquals('wechat', $strategy['type']);
    }

    public function testBatchHandleNotificationResultsWithEmptyBatches()
    {
        $batches = $this->getNotificationService()->batchHandleNotificationResults(array());
        $this->assertEmpty($batches);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage 获取发送结果错误
     */
    public function testBatchHandleNotificationResultsException()
    {
        $batches = array(
            array(
                'status' => 'unfinished',
                'sn' => 'test',
            ),
        );

        $biz = $this->getBiz();
        $mockNotificationService = \Mockery::mock('QiQiuYun\SDK\Service\NotificationService');
        $exception = new \Exception();
        $mockNotificationService->shouldReceive('batchGetNotifications')->andThrow($exception);
        $biz['qiQiuYunSdk.notification'] = $mockNotificationService;

        $this->getNotificationService()->batchHandleNotificationResults($batches);
    }

    public function testBatchHandleNotificationResultsWithEmptyResultData()
    {
        $batches = array(
            array(
                'status' => 'unfinished',
                'sn' => 'test',
            ),
        );
        $biz = $this->getBiz();
        $mockNotificationService = \Mockery::mock('QiQiuYun\SDK\Service\NotificationService');
        $mockNotificationService->shouldReceive('batchGetNotifications')->andReturn(array('data' => ''));
        $biz['qiQiuYunSdk.notification'] = $mockNotificationService;

        $result = $this->getNotificationService()->batchHandleNotificationResults($batches);
        $this->assertEmpty($result);
    }

    public function testCreateWeChatNotificationRecord()
    {
        $data = array('userName' => array('value' => 'testName'));
        $result = $this->getNotificationService()->createWeChatNotificationRecord('testSn', 'oneHourBeforeLiveOpen', $data);
        $this->assertEquals('testSn', $result['sn']);

        $event = $this->getNotificationService()->getEvent($result['eventId']);
        $this->assertNotFalse(strpos($event['content'], 'testName'));
    }

    protected function createBatch($fields = array())
    {
        $defaultFields = array(
            'sn' => 'test12345',
            'eventId' => 1,
            'strategyId' => 1,
        );
        $fields = array_merge($defaultFields, $fields);

        return $this->getNotificationService()->createBatch($fields);
    }

    protected function createEvent($fields = array())
    {
        $defaultFields = array(
            'title' => 'test Events',
            'content' => 'test Contents',
            'totalCount' => 10,
            'status' => 'created',
        );

        $fields = array_merge($defaultFields, $fields);

        return $this->getNotificationService()->createEvent($fields);
    }

    protected function createStrategy($fields = array())
    {
        $defaultFields = array(
            'eventId' => 1,
            'type' => 'wechat',
            'seq' => 1,
        );

        $fields = array_merge($defaultFields, $fields);

        return $this->getNotificationService()->createStrategy($fields);
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->createService('Notification:NotificationService');
    }
}
