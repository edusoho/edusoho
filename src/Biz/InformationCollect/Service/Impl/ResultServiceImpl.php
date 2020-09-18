<?php

namespace Biz\InformationCollect\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\InformationCollect\Dao\ResultDao;
use Biz\InformationCollect\Dao\ResultItemDao;
use Biz\InformationCollect\Service\ResultService;

class ResultServiceImpl extends BaseService implements ResultService
{
    public function countGroupByEventId($eventIds)
    {
        $counts = $this->getResultDao()->countGroupByEventId($eventIds);

        return ArrayToolkit::index($counts, 'eventId');
    }

    /**
     * @return ResultDao
     */
    protected function getResultDao()
    {
        return $this->createDao('InformationCollect:ResultDao');
    }

    /**
     * @return ResultItemDao
     */
    protected function getResultItemDao()
    {
        return $this->createDao('InformationCollect:ResultItemDao');
    }
}
