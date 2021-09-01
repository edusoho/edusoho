<?php


namespace Biz\OperationStatistic\Service\Impl;


use Biz\BaseService;
use Biz\OperationStatistic\Dao\OperationCountStatisticDao;
use Biz\OperationStatistic\Service\OperationCountStatisticService;

class OperationCountStatisticServiceImpl extends BaseService implements OperationCountStatisticService
{

    /**
     * @return OperationCountStatisticDao
     */
    protected function getOperationCountStatisticDao()
    {
        return $this->createDao('OperationStatistic:OperationCountStatisticDao');
    }
}