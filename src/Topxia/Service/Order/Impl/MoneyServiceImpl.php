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
        return $this->getMoneyDao()->searchMoneyRecordsCount($conditions);
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
        $incomes = $this->getMoneyDao()->searchMoneyRecords($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($incomes, 'id');
    }


    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

    private function getMoneyDao()
    {
        return $this->createDao('Order.MoneyDao');
    }
}