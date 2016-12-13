<?php

namespace Biz\User\Dao;

interface UpgradeNoticeDao
{
    public function getNotice($id);

    public function getNoticeByUserIdAndVersionAndCode($userId, $version, $code);

    public function addNotice($fields);

    public function updateNotice($id, $fields);

    public function deleteStatus($id);
}
