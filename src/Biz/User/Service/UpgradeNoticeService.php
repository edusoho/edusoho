<?php
namespace Biz\User;

interface UpgradeNoticeService
{
    public function getNotice($id);

    public function getNoticeByUserIdAndVersionAndCode($userId, $version, $code);

    public function addNotice($fields);
}
