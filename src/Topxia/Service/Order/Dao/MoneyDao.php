<?php

namespace Topxia\Service\Order\Dao;

interface MoneyDao
{

    public function searchIncomeCount($conditions);

    public function searchIncomes($conditions, $orderBy, $start, $limit);

}