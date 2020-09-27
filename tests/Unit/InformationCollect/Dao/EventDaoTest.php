<?php

namespace Tests\Unit\InformationCollect\Dao;

use Biz\BaseTestCase;

class EventDaoTest extends BaseTestCase
{
    public function testGetByActionAndLocation()
    {
        $mockEvent = $this->mockEvent();

        $event = $this->getInformationCollectEventDao()->getByActionAndLocation('buy_after', [
            'targetType' => 'course',
            'targetId' => '1',
        ]);

        $this->assertEquals($mockEvent['id'], $event['id']);
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
            'creator' => $this->getCurrentUser()->getId(),
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
}
