<?php

namespace Biz\InformationCollect\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\InformationCollect\Dao\LocationDao;
use Biz\InformationCollect\Service\LocationService;
use Biz\InformationCollect\TargetType\TargetTypeFactory;

class LocationServiceImpl extends BaseService implements LocationService
{
    public function getCollectLocations($eventIds)
    {
        $locations = $this->getLocationDao()->findByEventIds($eventIds);

        $targetTypeObject = new TargetTypeFactory();
        foreach ($locations as &$location) {
            if (0 != $location['targetId']) {
                $location['targetInfo'] = $targetTypeObject->create($location['targetType'])->getTargetInfo($location['targetId']);
            }
        }

        return ArrayToolkit::index($locations, 'eventId');
    }

    /**
     * @return LocationDao
     */
    protected function getLocationDao()
    {
        return $this->createDao('InformationCollect:LocationDao');
    }
}
