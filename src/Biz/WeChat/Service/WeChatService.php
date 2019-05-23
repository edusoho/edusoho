<?php

namespace Biz\WeChat\Service;

interface WeChatService
{
    const OFFICIAL_TYPE = 'official'; //公众号

    const OPEN_TYPE = 'open_app'; //开放平台应用

    const LANG = 'zh_CN'; //国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语，默认为zh-CN

    const REFRESH_NUM = 200;

    const FRESH_TIME = 7200; //过期时间 2小时

    public function batchSyncOfficialWeChatUsers();

    public function getOfficialWeChatUserByUserId($userId);

    public function batchFreshOfficialWeChatUsers($weChatUsers);

    public function refreshOfficialWeChatUsers($lifeTime = 86400, $refreshNum = self::REFRESH_NUM);
}
