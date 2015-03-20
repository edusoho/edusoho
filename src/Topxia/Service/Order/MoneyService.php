<?php

namespace Topxia\Service\Order;

interface MoneyService {

	public function searchMoneyRecordsCount($conditions);

	public function searchMoneyRecords($conditions, $sort = 'latest', $start, $limit);

}