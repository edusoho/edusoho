<?php

namespace Topxia\Service\User\Dao;

interface UpgradeNoticeDao
{
    public function getNotice($id);

    public function getNoticeByUserIdAndVersionAndCode($userId, $version, $code);

    public function addNotice($fields);

    public function updateNotice($id, $fields);

    public function deleteStatus($id);
}
