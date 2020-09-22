<?php

namespace Tests\Unit\InformationCollect\Service;

use Biz\BaseTestCase;
use Biz\InformationCollect\Dao\EventDao;
use Biz\InformationCollect\Dao\LocationDao;
use Biz\InformationCollect\Service\EventService;

class EventServiceTest extends BaseTestCase
{
    public function testGetEventByActionAndLocation()
    {
        $mockEvent = $this->mockEvent();

        $event = $this->getInformationCollectEventService()->getEventByActionAndLocation('buy_after', [
            'targetType' => 'course',
            'targetId' => '1',
        ]);

        $this->assertEquals($mockEvent['id'], $event['id']);
    }

    public function testGet()
    {
        $this->mockEvent();
        $result = $this->getInformationCollectEventService()->get(1);

        $this->assertEquals('测试表单', $result['formTitle']);
    }

    public function testCount()
    {
        $this->mockEvents();
        $result = $this->getInformationCollectEventService()->count(['title' => '表单']);

        $this->assertEquals(3, $result);
    }

    public function testSearch()
    {
        $this->mockEvents();
        $result = $this->getInformationCollectEventService()->search(['title' => '表单'], [], 0, PHP_INT_MAX);

        $this->assertEquals(3, count($result));
    }

    public function testCloseCollection()
    {
        $event = $this->mockEvent();
        $result = $this->getInformationCollectEventService()->closeCollection($event['id']);

        $this->assertEquals('close', $result['status']);
    }

    public function testOpenCollection()
    {
        $event = $this->mockEvent();
        $this->getInformationCollectEventService()->closeCollection($event['id']);
        $result = $this->getInformationCollectEventService()->openCollection($event['id']);

        $this->assertEquals('open', $result['status']);
    }

    public function testGetEventLocations()
    {
        $this->mockEvents();
        $result = $this->getInformationCollectEventService()->getEventLocations(1);

        $this->assertEquals([1,2], $result['course']);
    }

    protected function mockEvent()
    {
        $event = $this->getInformationCollectEventDao()->create([
            'id' => '1',
            'title' => '测试表单',
            'action' => 'buy_after',
            'formTitle' => '测试表单',
            'status' => 'open',
            'allowSkip' => 1,
            'creator' => 1,
        ]);

        $this->getInformationCollectLocationDao()->create([
            'eventId' => '1',
            'action' => 'buy_after',
            'targetType' => 'course',
            'targetId' => '1',
        ]);

        return $event;
    }

    protected function mockEvents()
    {
        $event = $this->getInformationCollectEventDao()->batchCreate([
            [
                'id' => '1',
                'title' => '测试表单1',
                'action' => 'buy_after',
                'formTitle' => '测试表单1',
                'status' => 'open',
                'allowSkip' => 1,
                'creator' => 1,
            ],
            [
                'id' => '2',
                'title' => '测试表单2',
                'action' => 'buy_after',
                'formTitle' => '测试表单2',
                'status' => 'open',
                'allowSkip' => 1,
                'creator' => 1,
            ],
            [
                'id' => '3',
                'title' => '测试表单3',
                'action' => 'buy_after',
                'formTitle' => '测试表单3',
                'status' => 'open',
                'allowSkip' => 1,
                'creator' => 1,
            ],
        ]);

        $this->getInformationCollectLocationDao()->batchCreate([
            [
                'eventId' => '1',
                'action' => 'buy_after',
                'targetType' => 'course',
                'targetId' => '1',
            ],
            [
                'eventId' => '1',
                'action' => 'buy_after',
                'targetType' => 'course',
                'targetId' => '2',
            ],
            [
                'eventId' => '2',
                'action' => 'buy_after',
                'targetType' => 'course',
                'targetId' => '0',
            ],
            [
                'eventId' => '3',
                'action' => 'buy_after',
                'targetType' => 'classroom',
                'targetId' => '0',
            ],
        ]);

        return $event;
    }

    /**
     * @return EventService
     */
    protected function getInformationCollectEventService()
    {
        return $this->createService('InformationCollect:EventService');
    }

    /**
     * @return EventDao
     */
    protected function getInformationCollectEventDao()
    {
        return $this->createDao('InformationCollect:EventDao');
    }

    /**
     * @return LocationDao
     */
    protected function getInformationCollectLocationDao()
    {
        return $this->createDao('InformationCollect:LocationDao');
    }
}
