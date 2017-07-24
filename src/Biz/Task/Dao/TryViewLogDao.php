<?php

namespace Biz\Task\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TryViewLogDao extends GeneralDaoInterface
{
    public function searchLogCountsByConditionsGroupByCreatedTimeWithFormat($conditions, $format = '%Y-%m-%d');
}
