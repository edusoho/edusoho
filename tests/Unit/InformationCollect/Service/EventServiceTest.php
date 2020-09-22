<?php

namespace Tests\Unit\InformationCollect\Service;

use Biz\BaseTestCase;

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

    public function testFindItemsByEventId()
    {
        $this->getInformationCollectItemDao()->batchCreate([
            ['id' => 1, 'eventId' => 1, 'code' => 'name', 'labelName' => '姓名', 'seq' => 1, 'required' => 1],
            ['id' => 2, 'eventId' => 1, 'code' => 'gender', 'labelName' => '性别', 'seq' => 2, 'required' => 1],
            ['id' => 3, 'eventId' => 2, 'code' => 'gender', 'labelName' => '性别', 'seq' => 2, 'required' => 1],
        ]);

        $items = $this->getInformationCollectEventService()->findItemsByEventId(1);

        $this->assertEquals(2, count($items));
        $this->assertEquals($items[0]['seq'], 1);
        $this->assertEquals($items[1]['seq'], 2);
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
        ]);

        $this->getInformationCollectLocationDao()->create([
            'eventId' => '1',
            'action' => 'buy_after',
            'targetType' => 'course',
            'targetId' => '1',
        ]);

        return $event;
    }

    protected function getInformationCollectEventService()
    {
        return $this->createService('InformationCollect:EventService');
    }

    protected function getInformationCollectEventDao()
    {
        return $this->createDao('InformationCollect:EventDao');
    }

    protected function getInformationCollectLocationDao()
    {
        return $this->createDao('InformationCollect:LocationDao');
    }

    protected function getInformationCollectItemDao()
    {
        return $this->createDao('InformationCollect:ItemDao');
    }
}
