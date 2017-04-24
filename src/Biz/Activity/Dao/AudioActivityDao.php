<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface AudioActivityDao extends GeneralDaoInterface
{
    public function findByIds($Ids);
}
