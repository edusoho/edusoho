<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TextActivityDao extends GeneralDaoInterface
{
    public function findByIds($Ids);
}
