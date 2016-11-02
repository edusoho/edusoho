<?php

namespace Biz\LiveActivity\Dao\Impl;

use Biz\LiveActivity\Dao\LiveActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class LiveActivityDaoImpl extends GeneralDaoImpl implements LiveActivityDao
{
    protected $table = 'live_activity';

    public function declares()
    {
    }

}
