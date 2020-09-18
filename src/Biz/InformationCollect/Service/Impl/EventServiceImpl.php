<?php

namespace Biz\InformationCollect\Service\Impl;

use Biz\BaseService;
use Biz\InformationCollect\Dao\EventDao;
use Biz\InformationCollect\Dao\ItemDao;
use Biz\InformationCollect\Dao\LocationDao;
use Biz\InformationCollect\Service\EventService;

class EventServiceImpl extends BaseService implements EventService
{
    public function count($conditions)
    {
        return $this->getEventDao()->count($conditions);
    }

    public function search($conditions, $orderBy, $start, $limit)
    {
        return $this->getEventDao()->search($conditions, $orderBy, $start, $limit);
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
