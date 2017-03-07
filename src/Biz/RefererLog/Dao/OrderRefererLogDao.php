<?php

namespace Biz\RefererLog\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OrderRefererLogDao extends GeneralDaoInterface
{
    public function searchOrderRefererLogs($conditions, $orderBy, $start, $limit, $groupBy);

    public function countOrderRefererLogs($conditions, $groupBy);

    public function countDistinctOrderRefererLogs($conditions, $distinctField);
}
