<?php

namespace Biz\User\Dao;

interface UpgradeNoticeDao
{
    public function getByUserIdAndVersionAndCode($userId, $version, $code);
}
