<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface VideoActivityDao extends GeneralDaoInterface
{
    public function findByIds($ids);
}
