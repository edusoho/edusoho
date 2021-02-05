<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TrainingActivityDao extends GeneralDaoInterface
{
    public function findByIds($Ids);
}
