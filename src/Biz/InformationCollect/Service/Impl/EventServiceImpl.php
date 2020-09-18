<?php

namespace Biz\InformationCollect\Service\Impl;

use Biz\BaseService;
use Biz\InformationCollect\Dao\EventDao;
use Biz\InformationCollect\Dao\ItemDao;
use Biz\InformationCollect\Dao\LocationDao;

class EventServiceImpl extends BaseService
{
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
