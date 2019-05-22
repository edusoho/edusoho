<?php

namespace Biz\WeChat\Service;

interface WeChatService
{
    public function batchSyncOfficialWeChatUsers();

    public function getOfficialWeChatUserByUserId($userId);

    public function batchFreshOfficialWeChatUsers($weChatUsers);
}
