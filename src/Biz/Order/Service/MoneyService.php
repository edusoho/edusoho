<?php

namespace Biz\Order\Service;

interface MoneyService {

	public function countMoneyRecords($conditions);

	public function searchMoneyRecords($conditions, $sort, $start, $limit);

}