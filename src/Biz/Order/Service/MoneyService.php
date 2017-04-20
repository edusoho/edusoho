<?php

namespace Biz\Order\Service;

interface MoneyService
{
    /**
     * @param $conditions
     *
     * @return mixed
     * @before searchMoneyRecordsCount
     */
    public function countMoneyRecords($conditions);

    public function searchMoneyRecords($conditions, $sort, $start, $limit);
}
