<?php

namespace Biz\InformationCollect\Service\Impl;

use Biz\BaseService;
use Biz\InformationCollect\Dao\ResultDao;
use Biz\InformationCollect\Dao\ResultItemDao;

class ResultServiceImpl extends BaseService
{
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
