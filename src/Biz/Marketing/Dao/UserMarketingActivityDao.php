<?php

namespace Biz\Marketing\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserMarketingActivityDao extends GeneralDaoInterface
{
    public function findByJoinedIdAndType($joinedId, $type);
}
