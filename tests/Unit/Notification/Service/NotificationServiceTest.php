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

    public function testCreateBatch()
    {
        $batch = $this->createBatch();
        $this->assertEquals('test12345', $batch['sn']);
        $this->assertEquals(1, $batch['eventId']);
        $getBatch = $this->getNotificationService()->getBatch($batch['id']);
        $this->assertEquals($batch, $getBatch);
    }

    public function testFindEventsByIds()
    {
        $event = $this->createEvent();

        $result = $this->getNotificationService()->findEventsByIds(array($event['id']));

        $this->assertCount(1, $result);
    }

    public function testCreateEvent()
    {
        $event = $this->createEvent();
        $this->assertEquals('test Events', $event['title']);
    }

    public function testCreateStrategy()
    {
        $strategy = $this->createStrategy();
        $this->assertEquals('wechat', $strategy['type']);
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
