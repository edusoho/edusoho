<?php

namespace Codeages\Biz\Order\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OrderDao extends GeneralDaoInterface
{
    public function getBySn($sn, array $options = array());

    public function findByIds(array $ids);

    public function findBySns(array $orderSns);

    public function findByInvoiceSn($invoiceSn);

    public function countGroupByDate($conditions, $sort, $dateColumn = 'pay_time');

    public function sumGroupByDate($column, $conditions, $sort, $dateColumn = 'pay_time');

    public function sumPaidAmount($conditions);

    public function queryWithItemConditions($conditions, $orderBys, $start, $limit);

    public function queryCountWithItemConditions($conditions);
}
