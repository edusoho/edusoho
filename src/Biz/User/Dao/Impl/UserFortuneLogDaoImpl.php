<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\UserFortuneLogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserFortuneLogDaoImpl extends GeneralDaoImpl implements UserFortuneLogDao
{
    protected $table = 'user_fortune_log';

    public function declares()
    {
        return array(
        );
    }
}
