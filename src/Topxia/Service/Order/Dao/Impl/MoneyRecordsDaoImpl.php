<?php

namespace Topxia\Service\Order\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Order\Dao\MoneyRecordsDao;
use PDO;

class MoneyRecordsDaoImpl extends BaseDao implements MoneyRecordsDao
{
    protected $table = 'money_record';

    public function searchMoneyRecordsCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function searchMoneyRecords($conditions, $orderBy, $start, $limit)
    {
    	$this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    private function _createSearchQueryBuilder($conditions)
    {
        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'money_record')
            ->andWhere('userId = :userId')
            ->andWhere('type = :type')
            ->andWhere('status = :status')
            ->andWhere('transactionNo = :transactionNo');
    }

}