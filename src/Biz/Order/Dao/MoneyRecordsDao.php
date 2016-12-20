<?php

namespace Topxia\Service\Order\Dao;

interface MoneyRecordsDao
{

    public function searchMoneyRecordsCount($conditions);

    public function searchMoneyRecords($conditions, $orderBy, $start, $limit);

}