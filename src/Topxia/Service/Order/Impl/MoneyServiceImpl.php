<?php
namespace Topxia\Service\Order\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Order\MoneyService;
use Topxia\Common\ArrayToolkit;

class MoneyServiceImpl extends BaseService implements MoneyService
{

	public function searchMoneyRecordsCount($conditions)
    {	
    	$conditions = array_filter($conditions);
        return $this->getMoneyRecordsDao()->searchMoneyRecordsCount($conditions);
    }

    public function searchMoneyRecords($conditions, $sort = 'latest', $start, $limit)
    {
        $orderBy = array();
        if ($sort == 'latest') {
            $orderBy =  array('transactionTime', 'DESC');
        } else {
            $orderBy = array('transactionTime', 'DESC');
        }

        $conditions = array_filter($conditions);
        $moneyRecords = $this->getMoneyRecordsDao()->searchMoneyRecords($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($moneyRecords, 'id');
    }

    private function getMoneyRecordsDao()
    {
        return $this->createDao('Order.MoneyRecordsDao');
    }
}