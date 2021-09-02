<?php


namespace Biz\OperationStatistic\Service\Impl;


use Biz\BaseService;
use Biz\OperationStatistic\Dao\OperationDetailStatisticDao;
use Biz\OperationStatistic\Service\OperationDetailStatisticService;

class OperationDetailStatisticServiceImpl extends BaseService implements OperationDetailStatisticService
{

    /**
     * @return OperationDetailStatisticDao
     */
    protected function getOperationDetailStatisticDao()
    {
        return $this->createDao('OperationStatistic:OperationDetailStatisticDao');
    }
}