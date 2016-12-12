<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\LiveActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class LiveActivityDaoImpl extends GeneralDaoImpl implements LiveActivityDao
{
    protected $table = 'live_activity';

    public function declares()
    {
    }

}
