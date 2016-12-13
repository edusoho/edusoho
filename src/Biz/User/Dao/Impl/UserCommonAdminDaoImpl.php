<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\UserCommonAdminDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserCommonAdminDaoImpl extends GeneralDaoImpl implements UserCommonAdminDao
{
    protected $table = 'shortcut';

    public function findByUserId($userId)
    {
        return $this->findInField(array('userId' => $userId));
    }

    public function getByUserIdAndUrl($userId, $url)
    {
        return $this->findInField(array('userId' => $userId, 'url' => $url));
    }

    public function declares()
    {
        return array(
        );
    }
}
