<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface DocActivityDao extends GeneralDaoInterface
{
    public function findByIds($Ids);
}
