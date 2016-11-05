<?php
namespace Topxia\Service\Order\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Order\MoneyService;

class MoneyServiceImpl extends BaseService implements MoneyService
{
    public function searchMoneyRecordsCount($conditions)
    {
        $conditions = array_filter($conditions);
        return $this->getMoneyRecordsDao()->searchMoneyRecordsCount($conditions);
    }

    public function searchMoneyRecords($conditions, $sort, $start, $limit)
    {
        $orderBy = $this->checkOrderBy($sort);

        $conditions   = array_filter($conditions);
        $moneyRecords = $this->getMoneyRecordsDao()->searchMoneyRecords($conditions, $orderBy, $start, $limit);

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

    protected function getMoneyRecordsDao()
    {
        return $this->createDao('Order.MoneyRecordsDao');
    }
}
