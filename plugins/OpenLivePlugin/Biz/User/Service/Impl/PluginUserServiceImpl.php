<?php

namespace OpenLivePlugin\Biz\User\Service\Impl;

use Biz\User\Service\Impl\UserServiceImpl;
use OpenLivePlugin\Biz\User\Dao\PluginUserDao;
use OpenLivePlugin\Biz\User\Service\PluginUserService;

class PluginUserServiceImpl extends UserServiceImpl implements PluginUserService
{
    public function searchSpeakers($searchStr)
    {
        return $this->getPluginUserDao()->searchLiveSpeakers($searchStr);
    }


    /**
     * @return PluginUserDao
     */
    protected function getPluginUserDao()
    {
        return $this->createDao('OpenLivePlugin:User:PluginUserDao');
    }
}
