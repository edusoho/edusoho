<?php

namespace Biz\InformationCollect\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\InformationCollect\Dao\EventDao;
use Biz\InformationCollect\Dao\ItemDao;
use Biz\InformationCollect\Dao\LocationDao;
use Biz\InformationCollect\InformationCollectException;
use Biz\InformationCollect\Service\EventService;

class EventServiceImpl extends BaseService implements EventService
{
    public function createEventWithLocations(array $fields)
    {
        if (!ArrayToolkit::requireds($fields, ['title', 'action', 'formTitle', 'status', 'allowSkip'])) {
            throw $this->createServiceException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $event = ArrayToolkit::filter($fields, [
            'title' => '',
            'action' => '',
            'formTitle' => '',
            'status' => 'open',
            'allowSkip' => 1,
        ]);

        $event['creator'] = $this->getCurrentUser()->getId();

        $event = $this->getEventDao()->create($event);

        $this->editEventLocations($event, $fields);

        return $event;
    }

    public function updateEventWithLocations($id, $updateFields)
    {
        $event = $this->getEventDao()->get($id);
        if (empty($event)) {
            throw $this->createServiceException(InformationCollectException::NOTFOUND_COLLECTION());
        }

        $updateEventFields = ArrayToolkit::filter($updateFields, [
            'title' => '',
            'action' => '',
            'formTitle' => '',
            'status' => 'open',
            'allowSkip' => 1,
        ]);

        $event = $this->getEventDao()->update($id, $updateEventFields);
        $this->editEventLocations($event, $updateFields);
        return $event;
    }

    public function count($conditions)
    {
        return $this->getEventDao()->count($conditions);
    }

    public function search($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareConditions($conditions);

        return $this->getEventDao()->search($conditions, $orderBy, $start, $limit);
    }

    private function _prepareConditions($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if (0 == $value) {
                return true;
            }

            return !empty($value);
        });

        if (!empty($conditions['startDate'])) {
            $conditions['startDate'] = strtotime($conditions['startDate']);
        }

        if (!empty($conditions['endDate'])) {
            $conditions['endDate'] = strtotime($conditions['endDate']);
        }

        return $conditions;
    }

    public function getEventByActionAndLocation($action, array $location)
    {
        if (!ArrayToolkit::requireds($location, ['targetType', 'targetId'], true)) {
            return null;
        }

        return $this->getEventDao()->getByActionAndLocation($action, $location);
    }

    public function get($id)
    {
        return $this->getEventDao()->get($id);
    }

    public function findItemsByEventId($eventId)
    {
        return $this->getItemDao()->findByEventId($eventId);
    }

    public function closeCollection($id)
    {
        $collection = $this->get($id);
        if (empty($collection)) {
            $this->createNewException(InformationCollectException::NOTFOUND_COLLECTION());
        }

        return $this->getEventDao()->update($id, ['status' => 'close']);
    }

    public function openCollection($id)
    {
        $collection = $this->get($id);
        if (empty($collection)) {
            $this->createNewException(InformationCollectException::NOTFOUND_COLLECTION());
        }

        return $this->getEventDao()->update($id, ['status' => 'open']);
    }

    public function getEventLocations($id)
    {
        $collection = $this->get($id);
        if (empty($collection)) {
            $this->createNewException(InformationCollectException::NOTFOUND_COLLECTION());
        }

        $locations = $this->getLocationDao()->search(['eventId' => $id], [], 0, PHP_INT_MAX);

        $locationInfo = [
            'course' => [],
            'classroom' => []
        ];
        foreach ($locations as $location) {
            if ('course' == $location['targetType']) {
                $locationInfo['course'][] = $location['targetId'];
            } else {
                $locationInfo['classroom'][] = $location['targetId'];
            }
        }

        return $locationInfo;
    }

    private function editEventLocations($event, $locationFields)
    {
        if (empty($locationFields['targetTypes'])) {
            return;
        }

        $deleteTypes = array_diff([self::TARGET_TYPE_COURSE, self::TARGET_TYPE_CLASSROOM], $locationFields['targetTypes']);
        if (!empty($deleteTypes)) {
            $this->getLocationDao()->batchDelete(['targetTypes' => $deleteTypes, 'eventId' => $event['id']]);
        }

        foreach ($locationFields['targetTypes'] as $type) {
            if (!in_array($type, [self::TARGET_TYPE_COURSE, self::TARGET_TYPE_CLASSROOM])) {
                continue;
            }
            if ($type === self::TARGET_TYPE_COURSE) {
                $targetIds = empty($locationFields['courseIds']) ? [] : (is_array($locationFields['courseIds']) ? $locationFields['courseIds'] : json_decode($locationFields['courseIds'], true));
            } else {
                $targetIds = empty($locationFields['classroomIds']) ? [] : (is_array($locationFields['classroomIds']) ? $locationFields['classroomIds'] : json_decode($locationFields['classroomIds'], true));
            }

            $this->batchUnbindOtherEventLocations($event['id'], $type, $targetIds);
            $this->batchUpdateEventLocations($event['id'], $event['action'], $type, $targetIds);
        }
    }

    private function batchUnbindOtherEventLocations($eventId, $targetType, $targetIds)
    {
        if (empty($targetIds)) {
            return;
        }
        $otherBindedLocationConditions = ['targetType' => $targetType, 'targetIds' => $targetIds, 'excludeEventId' => $eventId];
        $otherBindedLocations = $this->getLocationDao()->search($otherBindedLocationConditions, [], 0, $this->getLocationDao()->count($otherBindedLocationConditions), ['id']);

        if (!empty($otherBindedLocations)) {
            $this->getLocationDao()->batchDelete(['ids' => array_values(array_column($otherBindedLocations, 'id'))]);
        }
    }

    private function batchUpdateEventLocations($eventId, $action, $targetType, array $targetIds)
    {
        if (empty($targetIds)) {
            return;
        }

        $conditions = ['targetType' => $targetType, 'eventId' => $eventId];
        $existedLocations = $this->getLocationDao()->search($conditions, [], 0, $this->getLocationDao()->count($conditions), ['targetId']);
        $existedLocationsTargetIds = array_values(array_column($existedLocations, 'targetId'));

        $deleteLocationTargetIds = array_diff($existedLocationsTargetIds, $targetIds);

        if (!empty($deleteLocationTargetIds)) {
            $this->getLocationDao()->batchDelete(['targetIds' => $deleteLocationTargetIds, 'targetType' => $targetType]);
        }

        $newLocationsTargetIds = array_diff($targetIds, $existedLocationsTargetIds);

        $locations = [];
        foreach ($newLocationsTargetIds as $targetId) {
            $locations[] = [
                'eventId' => $eventId,
                'targetType' => $targetType,
                'action' => $action,
                'targetId' => $targetId
            ];
        }

        if (!empty($locations)) {
            $this->getLocationDao()->batchCreate($locations);
        }
    }

    public function searchLocations(array $conditions, array $orderBys, $start = 0, $limit = 20, $columns = [])
    {
        return $this->getLocationDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function countLocations(array $conditions)
    {
        return $this->getLocationDao()->count($conditions);
    }

    /**
     * @return EventDao
     */
    protected function getEventDao()
    {
        return $this->createDao('InformationCollect:EventDao');
    }

    /**
     * @return LocationDao
     */
    protected function getLocationDao()
    {
        return $this->createDao('InformationCollect:LocationDao');
    }

    /**
     * @return ItemDao
     */
    protected function getItemDao()
    {
        return $this->createDao('InformationCollect:ItemDao');
    }
}
