<?php

namespace OpenLivePlugin\Biz\User\Dao;

use Biz\User\Dao\UserDao;

interface PluginUserDao extends UserDao
{
    public function searchLiveSpeakers($searchStr);
}