<?php

namespace Topxia\Service\Order\Dao;

interface MoneyDao
{

    public function searchMoneyRecordsCount($conditions);

    public function searchMoneyRecords($conditions, $orderBy, $start, $limit);

}