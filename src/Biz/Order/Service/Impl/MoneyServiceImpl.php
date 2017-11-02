<?php

namespace Biz\Order\Service\Impl;

use Biz\BaseService;
use Biz\MoneyCard\Dao\MoneyCardDao;
use Biz\Order\Service\MoneyService;
use AppBundle\Common\ArrayToolkit;

class MoneyServiceImpl extends BaseService implements MoneyService
{
    public function countMoneyRecords($conditions)
    {
        $conditions = array_filter($conditions);

        return $this->getMoneyRecordsDao()->count($conditions);
    }

    public function searchMoneyRecords($conditions, $sort, $start, $limit)
    {
        $orderBy = $this->checkOrderBy($sort);

        $conditions = array_filter($conditions);
        $moneyRecords = $this->getMoneyRecordsDao()->search($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($moneyRecords, 'id');
    }

    protected function checkOrderBy($sort)
    {
        if (is_array($sort)) {
            $orderBy = $sort;
        } else {
            $orderBy = array('transactionTime', 'DESC');
        }

        return $orderBy;
    }

    /**
     * @return MoneyCardDao
     */
    protected function getMoneyRecordsDao()
    {
        return $this->createDao('Order:MoneyRecordsDao');
    }
}
