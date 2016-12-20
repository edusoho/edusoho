<?php

namespace Biz\Order\Service;

interface MoneyService {

	public function searchMoneyRecordsCount($conditions);

	public function searchMoneyRecords($conditions, $sort, $start, $limit);

}