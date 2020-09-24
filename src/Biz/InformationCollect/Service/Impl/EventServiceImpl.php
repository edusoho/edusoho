<?php

namespace Biz\InformationCollect\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\InformationCollect\Dao\EventDao;
use Biz\InformationCollect\Dao\ItemDao;
use Biz\InformationCollect\Dao\LocationDao;
use Biz\InformationCollect\InformationCollectionException;
use Biz\InformationCollect\Service\EventService;

class EventServiceImpl extends BaseService implements EventService
{
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
        }
        );

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
            return (object) [];
        }

        return $this->getEventDao()->getEventByActionAndLocation($action, $location);
    }

    public function get($id)
    {
        return $this->getEventDao()->get($id);
    }

    public function closeCollection($id)
    {
        $collection = $this->get($id);
        if (empty($collection)) {
            $this->createNewException(InformationCollectionException::NOTFOUND_COLLECTION());
        }

        return $this->getEventDao()->update($id, ['status' => 'close']);
    }

    public function openCollection($id)
    {
        $collection = $this->get($id);
        if (empty($collection)) {
            $this->createNewException(InformationCollectionException::NOTFOUND_COLLECTION());
        }

        return $this->getEventDao()->update($id, ['status' => 'open']);
    }

    public function getEventLocations($id)
    {
        $collection = $this->get($id);
        if (empty($collection)) {
            $this->createNewException(InformationCollectionException::NOTFOUND_COLLECTION());
        }

        $locations = $this->getLocationDao()->search(['eventId' => $id], [], 0, PHP_INT_MAX);

        $locationInfo = [];
        foreach ($locations as $location) {
            if ('course' == $location['targetType']) {
                $locationInfo['course'][] = $location['targetId'];
            } else {
                $locationInfo['classroom'][] = $location['targetId'];
            }
        }

        return $locationInfo;
    }

    public function getItemsByEventId($eventId)
    {
        $collection = $this->get($eventId);
        if (empty($collection)) {
            $this->createNewException(InformationCollectionException::NOTFOUND_COLLECTION());
        }

        return $this->getItemDao()->search(['eventId' => $eventId], ['seq' => 'ASC'], 0, PHP_INT_MAX);
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
