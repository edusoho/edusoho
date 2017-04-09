<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\LiveActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class LiveActivityDaoImpl extends GeneralDaoImpl implements LiveActivityDao
{
    protected $table = 'activity_live';

    public function findLiveActivityByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function declares()
    {
    }
}
