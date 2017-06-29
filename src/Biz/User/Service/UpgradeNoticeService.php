<?php

namespace Biz\User\Service;

interface UpgradeNoticeService
{
    public function getNotice($id);

    public function getNoticeByUserIdAndVersionAndCode($userId, $version, $code);

    public function addNotice($fields);
}
