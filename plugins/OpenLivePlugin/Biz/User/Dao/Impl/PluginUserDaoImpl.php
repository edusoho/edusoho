<?php

namespace OpenLivePlugin\Biz\User\Dao\Impl;

use Biz\User\Dao\Impl\UserDaoImpl;
use OpenLivePlugin\Biz\User\Dao\PluginUserDao;

class PluginUserDaoImpl extends UserDaoImpl implements PluginUserDao
{
    public function searchLiveSpeakers($searchStr)
    {
        $searchStr = '%'.$searchStr.'%';

        $sql = "SELECT * FROM {$this->table} WHERE `roles` LIKE '%|ROLE_TEACHER|%' AND (`nickname` LIKE ? OR `verifiedMobile` LIKE ? OR `email` LIKE ?) ORDER BY `id` ASC LIMIT 6;";

        return $this->db()->fetchAll($sql,  [$searchStr, $searchStr, $searchStr]) ?: [];
    }
}