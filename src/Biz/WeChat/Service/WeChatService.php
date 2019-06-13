<?php

namespace Biz\WeChat\Service;

interface WeChatService
{
    const OFFICIAL_TYPE = 'official'; //公众号

    const OPEN_TYPE = 'open_app'; //开放平台应用

    const LANG = 'zh_CN'; //国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语，默认为zh-CN

    const REFRESH_NUM = 200;

    const FRESH_TIME = 1800; //过期时间 半小时

    const WECHAT_MAX_USER_COUNT = 10000;

    public function getWeChatUserByTypeAndUnionId($type, $unionId);

    public function getWeChatUserByTypeAndOpenId($type, $openId);

    public function batchSyncOfficialWeChatUsers($nextOpenId = '');

    public function getOfficialWeChatUserByUserId($userId);

    public function freshOfficialWeChatUserWhenLogin($user, $bind, $token);

    public function batchFreshOfficialWeChatUsers($weChatUsers);

    public function refreshOfficialWeChatUsers($lifeTime = self::FRESH_TIME, $refreshNum = self::REFRESH_NUM);

    public function handleCloudNotification($oldSetting, $newSetting, $loginConnect);

    public function getTemplateId($key);

    public function createWeChatUser($fields);

    public function updateWeChatUser($id, $fields);
}
