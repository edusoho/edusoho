<?php

namespace Biz\WeChat\Service;

interface WeChatService
{
    const OFFICIAL_TYPE = 'official'; //公众号

    const OPEN_TYPE = 'open_app'; //开放平台应用

    const LANG = 'zh_CN'; //国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语，默认为zh-CN

    const REFRESH_NUM = 100;

    const FRESH_TIME = 1800; //过期时间 半小时

    const WECHAT_MAX_USER_COUNT = 10000;

    public function getPreAuthUrl($platformType, $callbackUrl);

    public function saveWeChatTemplateSetting($key, $fields, $notificationType);

    public function getWeChatUserByTypeAndUnionId($type, $unionId);

    public function getWeChatUserByTypeAndOpenId($type, $openId);

    public function findWeChatUsersByUserId($userId);

    public function batchSyncOfficialWeChatUsers($nextOpenId = '');

    public function getOfficialWeChatUserByUserId($userId);

    public function freshOfficialWeChatUserWhenLogin($user, $bind, $token);

    public function freshOpenAppWeChatUserWhenLogin($user, $token);

    public function batchFreshOfficialWeChatUsers($weChatUsers);

    public function refreshOfficialWeChatUsers($lifeTime = self::FRESH_TIME, $refreshNum = self::REFRESH_NUM);

    public function handleCloudNotification($oldSetting, $newSetting, $loginConnect);

    public function getTemplateId($key, $scene = '');

    public function isSubscribeSmsEnabled($smsType = '');

    public function sendSubscribeSms($smsType, array $userIds, $templateId, array $params = [], $batchId = 0);

    public function sendSubscribeWeChatNotification($templateCode, $logName, $list, $batchId = 0);

    public function sendSubscribeWeChatNotificationLocal($templateCode, $logName, $notifications);

    public function getSubscribeTemplateId($templateCode, $scene = '');

    public function addTemplate($template, $key, $notificationType);

    public function deleteTemplate($template, $key, $notificationType);

    public function createWeChatUser($fields);

    public function updateWeChatUser($id, $fields);

    public function countWeChatUserJoinUser($conditions);

    public function searchWeChatUsersJoinUser($conditions, $orderBys, $start, $limit);

    public function findAllBindUserIds();

    public function searchWeChatUsers($conditions, $orderBys, $start, $limit, $columns);

    public function findWechatUsersByUserIds(array $userIds);

    public function getWeChatSendChannel();

    public function searchSubscribeRecords(array $conditions, array $orderBy, $start, $limit, array $columns = []);

    public function searchSubscribeRecordCount(array $conditions);

    public function findOnceSubscribeRecordsByTemplateCodeUserIds($templateCode, array $userIds);

    public function updateSubscribeRecordsByIds(array $ids, array $fields);

    public function synchronizeSubscriptionRecords();
}
