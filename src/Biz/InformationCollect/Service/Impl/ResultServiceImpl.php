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

    public function isSubmited($userId, $eventId)
    {
        return !empty($this->getResultByUserIdAndEventId($userId, $eventId));
    }

    public function getResultByUserIdAndEventId($userId, $eventId)
    {
        $result = $this->getResultDao()->getByUserIdAndEventId($userId, $eventId);

        if ($result) {
            $result['items'] = $this->findResultItemsByResultId($result['id']);
        }

        return $result;
    }

    public function findResultItemsByResultId($resultId)
    {
        return $this->getResultItemDao()->findByResultId($resultId);
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
