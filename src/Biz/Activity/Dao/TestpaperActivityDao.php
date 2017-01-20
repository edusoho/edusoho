<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TestpaperActivityDao extends GeneralDaoInterface
{
    public function findActivitiesByIds($ids);
}
