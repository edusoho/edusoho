<?php

namespace Topxia\Service\Order;

interface MoneyService {

	public function searchIncomeCount($conditions);

	public function searchIncomes($conditions, $sort = 'latest', $start, $limit);

}