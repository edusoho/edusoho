<?php

namespace Tests\Unit\InformationCollect\Service;

use Biz\BaseTestCase;
use Biz\InformationCollect\Dao\EventDao;
use Biz\InformationCollect\Dao\ItemDao;
use Biz\InformationCollect\Dao\LocationDao;
use Biz\InformationCollect\Service\EventService;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

class EventServiceTest extends BaseTestCase
{
    public function testCreateEventWithLocations_whenFieldInvalid_thenThrowException()
    {
        $this->expectException(ServiceException::class);

        $this->getInformationCollectEventService()->createEventWithLocations(['title' => 'test title']);
    }

    public function testCreateEventWithLocationsWithEmptyTargetTypes()
    {
        $event = $this->getMockedEvent();

        $eventFields = array_merge($event, [
            'items' => [['labelName' => '性别', 'code' => 'gender', 'required' => 1, 'seq' => 1]],
        ]);

        $result = $this->getInformationCollectEventService()->createEventWithLocations($eventFields);
        unset($result['id']);
        unset($result['createdTime']);
        unset($result['updatedTime']);
        $this->assertEquals($event, $result);
    }

    public function testCreateEventWithLocationsWithCourseLocations()
    {
        $event = $this->getMockedEvent();

        $eventFields = array_merge($event, [
            'targetTypes' => ['course'],
            'courseIds' => ['1', '2'],
            'classroomIds' => ['3', '4'],
            'items' => [['labelName' => '性别', 'code' => 'gender', 'required' => 1, 'seq' => 1]],
        ]);

        $result = $this->getInformationCollectEventService()->createEventWithLocations($eventFields);
        $locationsCourseCount = $this->getInformationCollectLocationDao()->count(['eventId' => $result['id'], 'targetType' => 'course']);
        $locationsClassroomCount = $this->getInformationCollectLocationDao()->count(['eventId' => $result['id'], 'targetType' => 'classroom']);

        unset($result['id']);
        unset($result['createdTime']);
        unset($result['updatedTime']);
        $this->assertEquals($event, $result);
        $this->assertEquals(2, $locationsCourseCount);
        $this->assertEquals(0, $locationsClassroomCount);
    }

    public function testUpdateEventWithLocations_whenEventNotFound_thenThrowException()
    {
        $this->expectException(ServiceException::class);
        $this->getInformationCollectEventService()->updateEventWithLocations(1, []);
    }

    public function testUpdateEventWithLocations()
    {
        $event = $this->createEvent();
        $courseLocation = $this->createLocation(['eventId' => $event['id']]);
        $classroomLocation = $this->createLocation(['eventId' => $event['id'], 'targetType' => 'classroom']);

        $eventFields = array_merge($event, [
            'title' => 'updated title',
            'targetTypes' => ['course', 'classroom'],
            'courseIds' => ['1', '2'],
            'classroomIds' => ['3', '4'],
        ]);
        $locationsCourseCountBefore = $this->getInformationCollectLocationDao()->count(['eventId' => $event['id'], 'targetType' => 'course']);
        $locationsClassroomCountBefore = $this->getInformationCollectLocationDao()->count(['eventId' => $event['id'], 'targetType' => 'classroom']);

        $result = $this->getInformationCollectEventService()->updateEventWithLocations($event['id'], $eventFields);

        $locationsCourseCountAfter = $this->getInformationCollectLocationDao()->count(['eventId' => $event['id'], 'targetType' => 'course']);
        $locationsClassroomCountAfter = $this->getInformationCollectLocationDao()->count(['eventId' => $event['id'], 'targetType' => 'classroom']);

        $this->assertEquals('updated title', $result['title']);
        $this->assertEquals(1, $locationsCourseCountBefore);
        $this->assertEquals(1, $locationsClassroomCountBefore);
        $this->assertEquals(2, $locationsCourseCountAfter);
        $this->assertEquals(2, $locationsClassroomCountAfter);
    }

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

        $this->assertEquals([1, 2], $result['course']);
    }

    public function testSearchLocations()
    {
        $location1 = $this->createLocation();
        $location2 = $this->createLocation(['targetType' => 'test type']);
        $location3 = $this->createLocation(['targetId' => '2']);
        $location4 = $this->createLocation(['targetId' => 0, 'eventId' => 2]);

        $result = $this->getInformationCollectEventService()->searchLocations(['targetId_LTE' => 0], [], 0, 4);
        $this->assertEquals([$location4], $result);

        $result = $this->getInformationCollectEventService()->searchLocations(['targetType' => $location2['targetType']], [], 0, 4);
        $this->assertEquals([$location2], $result);
    }

    private function getMockedEvent($event = [])
    {
        return array_merge([
            'title' => 'test title',
            'action' => 'test action',
            'formTitle' => 'test form title',
            'status' => 'open',
            'allowSkip' => '1',
            'creator' => $this->getCurrentUser()->getId(),
        ], $event);
    }

    private function getMockedLocation($location = [])
    {
        return array_merge([
            'eventId' => '1',
            'action' => 'test action',
            'targetType' => 'course',
            'targetId' => '1',
        ], $location);
    }

    private function createLocation($location = [])
    {
        return $this->getInformationCollectLocationDao()->create($this->getMockedLocation($location));
    }

    private function createEvent($event = [])
    {
        return $this->getInformationCollectEventDao()->create($this->getMockedEvent($event));
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

    protected function mockItem()
    {
        return $this->getInformationCollectItemDao()->create([
            'id' => 1,
            'eventId' => 1,
            'code' => '测试表单',
            'labelName' => '性别',
            'seq' => 1,
            'required' => 1,
        ]);
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

    /**
     * @return ItemDao
     */
    protected function getInformationCollectItemDao()
    {
        return $this->createDao('InformationCollect:ItemDao');
    }
}
