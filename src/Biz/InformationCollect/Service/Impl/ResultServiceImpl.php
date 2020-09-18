<?php

namespace Biz\InformationCollect\Service\Impl;

use Biz\BaseService;
use Biz\InformationCollect\Dao\ResultDao;
use Biz\InformationCollect\Dao\ResultItemDao;

class ResultServiceImpl extends BaseService
{
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
