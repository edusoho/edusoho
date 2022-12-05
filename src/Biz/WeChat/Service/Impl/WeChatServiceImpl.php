<?php

namespace Biz\WeChat\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;
use Biz\AppLoggerConstant;
use Biz\BaseService;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Common\CommonException;
use Biz\Sms\Service\SmsService;
use Biz\Sms\SmsScenes;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Biz\WeChat\Dao\SubscribeRecordDao;
use Biz\WeChat\Dao\UserWeChatDao;
use Biz\WeChat\Service\WeChatService;
use Biz\WeChat\WeChatException;
use Codeages\Biz\Framework\Dao\BatchCreateHelper;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use ESCloud\SDK\Service\NotificationService;
use QiQiuYun\SDK\Constants\NotificationChannelTypes;
use QiQiuYun\SDK\Constants\WeChatPlatformTypes;

class WeChatServiceImpl extends BaseService implements WeChatService
{
    /**
     * @param $platformType WeChatPlatformTypes
     * @param $callbackUrl
     *
     * @return mixed
     */
    public function getPreAuthUrl($platformType, $callbackUrl)
    {
        $preAuthUrl = $this->biz['ESCloudSdk.wechat']->getPreAuthUrl($platformType, $callbackUrl);

        return $preAuthUrl['url'];
    }

    public function saveWeChatTemplateSetting($key, $fields, $notificationType)
    {
        $settingName = 'wechat';
        if ('messageSubscribe' == $notificationType) {
            $settingName = 'wechat_notification';
        }
        $wechatSetting = $this->getSettingService()->get($settingName, []);
        if (!ArrayToolkit::requireds($fields, ['status'])) {
            throw new InvalidArgumentException('缺少必要字段');
        }

        $fields['scenes'] = empty($fields['scenes']) ? [] : $fields['scenes'];
        $wechatSetting['templates'][$key] = empty($wechatSetting['templates'][$key]) ? $fields : array_merge($wechatSetting['templates'][$key], $fields);

        $this->getSettingService()->set($settingName, $wechatSetting);
        $this->dispatchEvent('wechat.template_setting.save', new Event($fields, ['key' => $key, 'wechatSetting' => $wechatSetting, 'notificationType' => $notificationType]));

        return true;
    }

    public function getWeChatSendChannel()
    {
        $wechatSetting = $this->getSettingService()->get('wechat', []);
        if (empty($wechatSetting['is_authorization'])) {
            return 'wechat';
        }

        $notificationSetting = $this->getSettingService()->get('wechat_notification', ['notification_type' => 'serviceFollow']);

        return 'serviceFollow' === $notificationSetting['notification_type'] ? 'wechat_agent' : 'wechat_subscribe';
    }

    public function getWeChatUser($id)
    {
        return $this->getUserWeChatDao()->get($id);
    }

    public function getWeChatUserByTypeAndUnionId($type, $unionId)
    {
        return $this->getUserWeChatDao()->getByTypeAndUnionId($type, $unionId);
    }

    public function getWeChatUserByTypeAndOpenId($type, $openId)
    {
        return $this->getUserWeChatDao()->getByTypeAndOpenId($type, $openId);
    }

    public function findWeChatUsersByUserId($userId)
    {
        return $this->getUserWeChatDao()->findByUserId($userId);
    }

    public function findSubscribedUsersByUserIdsAndType($userIds, $type)
    {
        return $this->getUserWeChatDao()->findSubscribedUsersByUserIdsAndType($userIds, $type);
    }

    public function findWeChatUsersByUserIdAndType($userId, $type)
    {
        return $this->getUserWeChatDao()->findByUserIdAndType($userId, $type);
    }

    public function countWeChatUserJoinUser($conditions)
    {
        return $this->getUserWeChatDao()->countWeChatUserJoinUser($conditions);
    }

    public function getOfficialWeChatUserByUserId($userId)
    {
        $weChatUser = $this->getUserWeChatDao()->getByUserIdAndType($userId, self::OFFICIAL_TYPE);
        if (empty($weChatUser)) {
            return [];
        }

        if ($weChatUser['lastRefreshTime'] < time() - self::FRESH_TIME) {
            try {
                $this->freshOfficialWeChatUser($weChatUser);
            } catch (\Exception $e) {
                $this->getLogger()->error($e->getMessage());
            }
        }

        return $this->getUserWeChatDao()->getByUserIdAndType($userId, self::OFFICIAL_TYPE);
    }

    public function createWeChatUser($fields)
    {
        if (!ArrayToolkit::requireds($fields, ['appId', 'type'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $weChatUser = $this->weChatUserFilter($fields);

        return $this->getUserWeChatDao()->create($weChatUser);
    }

    public function updateWeChatUser($id, $fields)
    {
        $weChatUser = $this->getWeChatUser($id);
        if (empty($weChatUser)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $updateFields = $this->weChatUserFilter($fields);
        $this->getUserWeChatDao()->update($id, $updateFields);
    }

    public function searchWeChatUsersJoinUser($conditions, $orderBys, $start, $limit)
    {
        return $this->getUserWeChatDao()->searchWeChatUsersJoinUser($conditions, $orderBys, $start, $limit);
    }

    public function findAllBindUserIds()
    {
        return $this->getUserWeChatDao()->findAllBindUserIds();
    }

    public function searchWeChatUsers($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getUserWeChatDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function batchSyncOfficialWeChatUsers($nextOpenId = '')
    {
        $biz = $this->biz;
        $weChatSetting = $this->getSettingService()->get('wechat', []);
        if (!empty($weChatSetting['is_authorization'])) {
            $weChatUsersList = $this->getSDKWeChatService()->getUserList($nextOpenId);
        } else {
            $weChatUsersList = $biz['wechat.template_message_client']->getUserList($nextOpenId);
        }
        if (!empty($weChatUsersList['data']['openid'])) {
            $openIds = array_values($weChatUsersList['data']['openid']);
            $existOpenIds = ArrayToolkit::column($this->getUserWeChatDao()->findOpenIdsInListsByType($openIds, self::OFFICIAL_TYPE), 'openId');
            $unExistOpenIds = array_diff($openIds, $existOpenIds);

            if (empty($unExistOpenIds)) {
                return;
            }

            $saveData = [];
            $appId = $biz['wechat.template_message_client']->getAppId();
            foreach ($unExistOpenIds as $openId) {
                $saveData[] = [
                    'appId' => $appId,
                    'type' => self::OFFICIAL_TYPE,
                    'openId' => $openId,
                ];
            }

            if (!empty($saveData)) {
                $this->getUserWeChatDao()->batchCreate($saveData);
            }
        }

        return [
            'next_openid' => $weChatUsersList['next_openid'],
        ];
    }

    public function refreshOfficialWeChatUsers($lifeTime = WeChatService::FRESH_TIME, $refreshNum = self::REFRESH_NUM)
    {
        $conditions = [
            'type' => WeChatService::OFFICIAL_TYPE,
            'lastRefreshTime_LT' => time() - $lifeTime,
        ];
        $weChatUsers = $this->searchWeChatUsers(
            $conditions,
            ['lastRefreshTime' => 'ASC'],
            0,
            $refreshNum,
            ['id', 'openId', 'unionId', 'userId']
        );

        if (empty($weChatUsers)) {
            return;
        }

        $this->batchFreshOfficialWeChatUsers($weChatUsers);
    }

    public function freshOfficialWeChatUserWhenLogin($user, $bind, $token)
    {
        if (empty($user)) {
            return;
        }

        try {
            $weChatUser = $this->getWeChatUserByTypeAndOpenId(WeChatService::OFFICIAL_TYPE, $token['openid']);
            if (empty($weChatUser)) {
                $this->createWeChatUser([
                    'type' => WeChatService::OFFICIAL_TYPE,
                    'appId' => $this->getSettingService()->node('login_bind.weixinmob_key', ''),
                    'unionId' => $bind['fromId'],
                    'openId' => $token['openid'],
                    'userId' => $user['id'],
                ]);
            } elseif ($weChatUser['id'] != $user['id']) {
                $this->updateWeChatUser($weChatUser['id'], [
                    'userId' => $user['id'],
                ]);
            }
        } catch (\Exception $e) {
            $this->getLogger()->error('WeChatFreshOfficialUser_'.$e->getMessage(), $e->getTrace());
        }
    }

    public function freshOfficialWeChatUser($weChatUser)
    {
        $biz = $this->biz;
        $wechatSetting = $this->getSettingService()->get('wechat', []);
        if (!empty($wechatSetting['is_authorization']) && 1 == $wechatSetting['is_authorization']) {
            $freshWeChatUser = $this->getSDKWeChatService()->getUserInfo($weChatUser['openId']);
        } else {
            $freshWeChatUser = $biz['wechat.template_message_client']->getUserInfo($weChatUser['openId']);
        }
        $unionId = !empty($freshWeChatUser['unionid']) ? $freshWeChatUser['unionid'] : $weChatUser['unionId'];

        $userBind = $this->getUserService()->getUserBindByTypeAndUserId('weixin', $weChatUser['userId']);

        if (empty($userBind['fromId']) || $userBind['fromId'] != $unionId) {
            $userBind = $this->getUserService()->getUserBindByTypeAndFromId('weixin', $weChatUser['unionId']);
        }
        $userId = !empty($unionId) && ($userBind['fromId'] == $unionId) ? $userBind['toId'] : 0;

        $updateField = [
            'unionId' => $unionId,
            'userId' => $userId,
            'data' => $freshWeChatUser,
            'isSubscribe' => empty($freshWeChatUser['subscribe']) ? 0 : $freshWeChatUser['subscribe'],
            'lastRefreshTime' => time(),
            'nickname' => empty($freshWeChatUser['nickname']) ? '' : urlencode($freshWeChatUser['nickname']),
            'profilePicture' => empty($freshWeChatUser['headimgurl']) ? '' : $freshWeChatUser['headimgurl'],
            'subscribeTime' => empty($freshWeChatUser['subscribe_time']) ? 0 : $freshWeChatUser['subscribe_time'],
        ];

        $this->updateWeChatUser($weChatUser['id'], $updateField);
    }

    public function batchFreshOfficialWeChatUsers($weChatUsers)
    {
        $biz = $this->biz;
        $wechatSetting = $this->getSettingService()->get('wechat', []);
        if (!empty($wechatSetting['is_authorization'])) {
            $freshWeChatUsers = $this->getSDKWeChatService()->batchGetUserInfo(ArrayToolkit::column($weChatUsers, 'openId'));
        } else {
            $userList = $this->convertWeChatUsersToOfficialRequestParams($weChatUsers);
            $freshWeChatUsers = $biz['wechat.template_message_client']->batchGetUserInfo($userList);
        }

        $freshWeChatUsers = ArrayToolkit::index($freshWeChatUsers, 'openid');

        $userIds = ArrayToolkit::column($weChatUsers, 'userId');
        $unionIds = ArrayToolkit::column($weChatUsers, 'unionId');

        $userBindsByToIds = $this->getUserService()->findUserBindByTypeAndToIds('weixin', $userIds);
        $userBindsByToIds = ArrayToolkit::index($userBindsByToIds, 'fromId');
        $userBindsByUnionIds = $userBinds = $this->getUserService()->findUserBindByTypeAndFromIds('weixin', $unionIds);
        $userBindsByUnionIds = ArrayToolkit::index($userBindsByUnionIds, 'fromId');

        $userBinds = array_merge($userBindsByToIds, $userBindsByUnionIds);
        $batchUpdateHelper = new BatchUpdateHelper($this->getUserWeChatDao());
        foreach ($weChatUsers as $weChatUser) {
            $freshWeChatUser = isset($freshWeChatUsers[$weChatUser['openId']]) ? $freshWeChatUsers[$weChatUser['openId']] : [];

            $unionId = !empty($freshWeChatUser['unionid']) ? $freshWeChatUser['unionid'] : $weChatUser['unionId'];

            $userId = !empty($unionId) && !empty($userBinds[$unionId]) ? $userBinds[$unionId]['toId'] : 0;
            $updateField = [
                'unionId' => $unionId,
                'userId' => $userId,
                'data' => $freshWeChatUser,
                'isSubscribe' => empty($freshWeChatUser['subscribe']) ? 0 : $freshWeChatUser['subscribe'],
                'lastRefreshTime' => time(),
                'nickname' => empty($freshWeChatUser['nickname']) ? '' : urlencode($freshWeChatUser['nickname']),
                'profilePicture' => empty($freshWeChatUser['headimgurl']) ? '' : $freshWeChatUser['headimgurl'],
                'subscribeTime' => empty($freshWeChatUser['subscribe_time']) ? 0 : $freshWeChatUser['subscribe_time'],
            ];
            $batchUpdateHelper->add('id', $weChatUser['id'], $updateField);
        }

        $batchUpdateHelper->flush();
    }

    public function isSubscribeSmsEnabled($smsType = '')
    {
        $wechatSetting = $this->getSettingService()->get('wechat_notification', []);
        if (empty($wechatSetting['is_authorization']) || 'messageSubscribe' !== $wechatSetting['notification_type']) {
            return false;
        }

        $smsSetting = $this->getSettingService()->get('cloud_sms');
        if (empty($smsSetting['sms_enabled'])) {
            return false;
        }

        if ($smsType && $this->getSmsService()->isOpen($smsType)) {
            return false;
        }

        if (empty($wechatSetting['notification_sms'])) {
            return false;
        }

        return !empty($wechatSetting['templates'][$smsType]['status']);
    }

    public function sendSubscribeSms($smsType, array $userIds, $templateId, array $params = [])
    {
        if (empty($userIds)) {
            return [];
        }

        if (!$this->isSubscribeSmsEnabled($smsType)) {
            return $this->getLogger()->info('微信订阅消息短信发送服务未开启');
        }

        $mobiles = $this->getUserService()->findUnlockedUserMobilesByUserIds($userIds);
        if (empty($mobiles)) {
            return [];
        }

        try {
            $result = $this->getSmsNotificationClient()->sendToMany([
                'mobiles' => implode(',', $mobiles),
                'templateId' => $templateId,
                'templateParams' => $params,
                'tag' => SmsScenes::WECHAT_SUBSCRIBE,
            ]);
        } catch (\Exception $e) {
            $this->getLogService()->error(AppLoggerConstant::NOTIFY, 'send_wechat_sms_notification', "发送微信通知失败:template:{$smsType}", ['error' => $e->getMessage()]);

            return [];
        }

        if (empty($result['sn'])) {
            $this->getLogService()->error(AppLoggerConstant::NOTIFY, 'send_wechat_sms_notification', "发送微信通知失败:template:{$smsType}", $result);

            return [];
        }

        return $this->getNotificationService()->createSmsNotificationRecord($params, ['key' => $smsType, 'smsTemplateId' => $templateId, 'sendNum' => count($mobiles)], 'wechat_subscribe');
    }

    public function sendSubscribeWeChatNotification($templateCode, $logName, $list, $batchId = 0)
    {
        try {
            $result = $this->getSDKNotificationService()->sendNotifications($list);
        } catch (\Exception $e) {
            $this->getLogService()->error(AppLoggerConstant::NOTIFY, 'send_wechat_subscribe_notification', "{$logName}:发送微信订阅通知失败:template:{$templateCode}", ['error' => $e->getMessage()]);

            return false;
        }

        if (empty($result['sn'])) {
            $this->getLogService()->error(AppLoggerConstant::NOTIFY, 'send_wechat_subscribe_notification', "{$logName}:发送微信订阅通知失败:template:{$templateCode}", $result);

            return false;
        }

        return $this->getNotificationService()->createWeChatNotificationRecord($result['sn'], $templateCode, $list[0]['template_args'], 'wechat_subscribe', $batchId);
    }

    /**
     * @param $templateCode
     * templateCode 模板code
     * @param string $scene
     *
     * @return mixed|null
     */
    public function getSubscribeTemplateId($templateCode, $scene = '')
    {
        $wechatSetting = $this->getSettingService()->get('wechat', []);
        if (empty($wechatSetting['wechat_notification_enabled'])) {
            return null;
        }

        $setting = $this->getSettingService()->get('wechat_notification', []);
        if (empty($setting['is_authorization']) || 'messageSubscribe' !== $setting['notification_type']) {
            return null;
        }

        $template = !empty($setting['templates'][$templateCode]) ? $setting['templates'][$templateCode] : [];

        if (empty($template['status']) || empty($template['templateId'])) {
            return null;
        }

        $scenes = empty($template['scenes']) ? [] : $template['scenes'];
        if (!empty($scene) && !in_array($scene, $scenes)) {
            return null;
        }

        return $template['templateId'];
    }

    /**
     * @param $key
     * key 模板key
     * @param string $scene
     *                      scene 模板对应场景
     *
     * @return mixed|null
     */
    public function getTemplateId($key, $scene = '')
    {
        $wechatSetting = $this->getSettingService()->get('wechat', []);
        if (empty($wechatSetting['wechat_notification_enabled'])) {
            return null;
        }

        $notificationSetting = $this->getSettingService()->get('wechat_notification', []);
        if (empty($notificationSetting['is_authorization']) || 'serviceFollow' !== $notificationSetting['notification_type']) {
            return null;
        }

        $template = !empty($wechatSetting['templates'][$key]) ? $wechatSetting['templates'][$key] : [];

        if (empty($template['status']) || empty($template['templateId'])) {
            return null;
        }

        $scenes = empty($template['scenes']) ? [] : $template['scenes'];
        if (!empty($scene) && !in_array($scene, $scenes)) {
            return null;
        }

        return $template['templateId'];
    }

    /**
     * @param $template
     * @param $key
     * @param $notificationType
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function addTemplate($template, $key, $notificationType)
    {
        $client = $this->getTemplateClient();
        if (empty($client)) {
            $this->createNewException(WeChatException::TOKEN_MAKE_ERROR());
        }

        $templateParam = [];
        $settingName = 'wechat';

        if ('messageSubscribe' == $notificationType) {
            $settingName = 'wechat_notification';
            $templateParam = [
                'templateType' => 'subscribe',
                'templateParams' => [
                    'kidList' => $template['kidList'],
                    'sceneDesc' => $template['sceneDesc'],
                ],
            ];
        }
        $wechatSetting = $this->getSettingService()->get($settingName);

        if (empty($wechatSetting['templates'][$key]['templateId'])) {
            try {
                if (!empty($wechatSetting['is_authorization'])) {
                    $data = $this->getSDKWeChatService()->createNotificationTemplate($template['id'], $templateParam);
                }
                if (empty($wechatSetting['is_authorization']) && 'serviceFollow' == $notificationType) {
                    $data = $client->addTemplate($template['id']);
                }
            } catch (\Exception $e) {
                if (40220005 == $e->getCode()) {
                    $this->createNewException(WeChatException::TEMPLATE_EXCEEDS_LIMIT());
                }

                if (40220007 == $e->getCode()) {
                    $this->createNewException(WeChatException::TEMPLATE_CONFLICT_INDUSTRY());
                }

                throw $e;
            }

            if (empty($data)) {
                $this->createNewException(WeChatException::TEMPLATE_OPEN_ERROR());
            }

            $wechatSetting['templates'][$key]['templateId'] = $data['template_id'];
        }

        $wechatSetting['templates'][$key]['status'] = 1;

        $this->getSettingService()->set($settingName, $wechatSetting);

        return $this->getSettingService()->get($settingName);
    }

    /**
     * @param $template
     * @param $key
     * @param $notificationType
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function deleteTemplate($template, $key, $notificationType)
    {
        $client = $this->getTemplateClient();
        if (empty($client)) {
            $this->createNewException(WeChatException::TOKEN_MAKE_ERROR());
        }

        $settingName = 'wechat';
        if ('messageSubscribe' == $notificationType) {
            $settingName = 'wechat_notification';
        }
        $wechatSetting = $this->getSettingService()->get($settingName);

        if (!empty($wechatSetting['templates'][$key]['templateId'])) {
            if ('serviceFollow' == $notificationType) {
                if (!empty($wechatSetting['is_authorization'])) {
                    $data = $this->getSDKWeChatService()->deleteNotificationTemplate($wechatSetting['templates'][$key]['templateId']);
                } else {
                    $data = $client->deleteTemplate($wechatSetting['templates'][$key]['templateId']);
                }
            }

            if ('messageSubscribe' == $notificationType) {
                $data = $this->getSDKWeChatService()->deleteNotificationTemplate($wechatSetting['templates'][$key]['templateId'], ['templateType' => 'subscribe']);
            }

            if (empty($data)) {
                $this->createNewException(WeChatException::TEMPLATE_OPEN_ERROR());
            }
        }

        $wechatSetting['templates'][$key]['templateId'] = '';
        $wechatSetting['templates'][$key]['status'] = 0;

        return $this->getSettingService()->set($settingName, $wechatSetting);
    }

    public function handleCloudNotification($oldSetting, $newSetting, $loginConnect)
    {
        if ($oldSetting['wechat_notification_enabled'] == $newSetting['wechat_notification_enabled']) {
            return true;
        }

        if (!$this->isCloudOpen()) {
            return false;
        }

        $biz = $this->biz;
        try {
            if (1 == $newSetting['wechat_notification_enabled']) {
                $biz['ESCloudSdk.notification']->openAccount();
                $wechatChannel = $biz['ESCloudSdk.notification']->openChannel(NotificationChannelTypes::WECHAT, [
                    'app_id' => $loginConnect['weixinmob_key'],
                    'app_secret' => $loginConnect['weixinmob_secret'],
                ]);
                $subscribeChannel = $biz['ESCloudSdk.notification']->openChannel(NotificationChannelTypes::WECHAT_SUBSCRIBE, [
                    'app_id' => $loginConnect['weixinmob_key'],
                    'app_secret' => $loginConnect['weixinmob_secret'],
                ]);
                $this->registerJobs();
            } else {
                $notificationSetting = $this->getSettingService()->get('wechat_notification', []);
                $biz['ESCloudSdk.notification']->closeAccount();
                $wechatChannel = $biz['ESCloudSdk.notification']->closeChannel(NotificationChannelTypes::WECHAT);
                $subscribeChannel = empty($notificationSetting) || 'messageSubscribe' != $notificationSetting['notification_type'] ? true : $biz['ESCloudSdk.notification']->closeChannel(NotificationChannelTypes::WECHAT_SUBSCRIBE);
                $this->deleteJobs();
            }
        } catch (\RuntimeException $e) {
            return false;
        }

        if (empty($wechatChannel) || empty($subscribeChannel)) {
            return false;
        }

        return true;
    }

    public function getJobs()
    {
        $jobs = [
            [
                'name' => 'WeChatUsersSyncJob',
                'expression' => '*/60 * * * *',
                'class' => 'Biz\WeChat\Job\WeChatUsersSync',
                'misfire_threshold' => 60 * 10,
            ],
            [
                'name' => 'WeChatUserFreshJob',
                'expression' => '*/5 * * * *',
                'class' => 'Biz\WeChat\Job\WeChatUserFreshJob',
                'misfire_threshold' => 60 * 5,
            ],
        ];

        return $jobs;
    }

    public function searchSubscribeRecords(array $conditions, array $orderBy, $start, $limit, array $columns = [])
    {
        return $this->getSubscribeRecordDao()->search($conditions, $orderBy, $start, $limit, $columns);
    }

    public function searchSubscribeRecordCount(array $conditions)
    {
        return $this->getSubscribeRecordDao()->count($conditions);
    }

    public function findOnceSubscribeRecordsByTemplateCodeUserIds($templateCode, array $userIds)
    {
        if (empty($userIds)) {
            return [];
        }

        $weChatUsers = $this->searchWeChatUsers(
            ['userIds' => $userIds],
            ['lastRefreshTime' => 'ASC'],
            0,
            count($userIds),
            ['id', 'openId', 'unionId', 'userId']
        );

        if (empty($weChatUsers)) {
            return [];
        }

        $records = [];
        foreach ($weChatUsers as $weChatUser) {
            $result = $this->searchSubscribeRecords(
                ['templateCode' => $templateCode, 'toId' => $weChatUser['openId'], 'templateType' => 'once', 'isSend_LT' => 1],
                ['id' => 'ASC'],
                0,
                1
            );
            if (empty($result)) {
                continue;
            }
            $records[] = array_merge($result[0], ['userId' => $weChatUser['userId']]);
        }

        return $records;
    }

    public function updateSubscribeRecordsByIds(array $ids, array $fields)
    {
        $updateFields = ArrayToolkit::filter($fields, ['isSend' => 0]);

        return $this->getSubscribeRecordDao()->update(['ids' => $ids], $updateFields);
    }

    public function synchronizeSubscriptionRecords()
    {
        $options = [
            'createdTime_GT' => $this->getLastCreatedTime(),
            'createdTime_LT' => time(),
        ];

        $offset = 0;
        $limit = 30;

        $synchronizeRecords = $this->getSDKNotificationService()->searchRecords($options, $offset);
        if (empty($synchronizeRecords['data'])) {
            return;
        }

        $total = $synchronizeRecords['paging']['total'];
        $totalPage = $total / $limit;
        for ($page = 0; $page <= $totalPage; ++$page) {
            $synchronizeRecords = $this->getSDKNotificationService()->searchRecords($options, $offset * $page, $limit);
            $batchUpdateHelper = new BatchCreateHelper($this->getSubscribeRecordDao());
            foreach ($synchronizeRecords['data'] as $record) {
                $createRecord = [
                    'toId' => $record['to_id'],
                    'templateCode' => $record['template_code'],
                    'templateType' => 'once',
                    'createdTime' => strtotime($record['created_time']),
                ];
                $batchUpdateHelper->add($createRecord);
            }
            $batchUpdateHelper->flush();
        }
    }

    protected function getLastCreatedTime()
    {
        $lastRecord = $this->getSubscribeRecordDao()->getLastRecord();

        return $lastRecord ? $lastRecord['createdTime'] : 0;
    }

    /**
     * @return SubscribeRecordDao
     */
    protected function getSubscribeRecordDao()
    {
        return $this->createDao('WeChat:SubscribeRecordDao');
    }

    /**
     * @return NotificationService
     */
    protected function getSDKNotificationService()
    {
        return $this->biz['ESCloudSdk.notification'];
    }

    protected function isCloudOpen()
    {
        try {
            $api = CloudAPIFactory::create('root');
            $info = $api->get('/me');
        } catch (\RuntimeException $e) {
            return false;
        }

        if (empty($info['accessCloud'])) {
            return false;
        }

        return true;
    }

    private function registerJobs()
    {
        $jobs = $this->getJobs();
        foreach ($jobs as $job) {
            $this->getSchedulerService()->register($job);
        }
    }

    private function deleteJobs()
    {
        $jobs = $this->getJobs();

        foreach ($jobs as $job) {
            $this->getSchedulerService()->deleteJobByName($job['name']);
        }
    }

    private function convertWeChatUsersToOfficialRequestParams($weChatUsers, $lang = self::LANG)
    {
        $userList = [];

        foreach ($weChatUsers as $user) {
            $userList[] = [
                'openid' => $user['openId'],
                'lang' => $lang,
            ];
        }

        return $userList;
    }

    private function getTemplateClient()
    {
        return $this->biz['wechat.template_message_client'];
    }

    private function weChatUserFilter($fields)
    {
        return ArrayToolkit::parts($fields, ['appId', 'type', 'userId', 'openId', 'unionId', 'data', 'lastRefreshTime']);
    }

    /**
     * @return UserWeChatDao
     */
    protected function getUserWeChatDao()
    {
        return $this->createDao('WeChat:UserWeChatDao');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('system:SettingService');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    /**
     * @return \QiQiuYun\SDK\Service\WeChatService
     */
    protected function getSDKWeChatService()
    {
        return $this->biz['ESCloudSdk.wechat'];
    }

    /**
     * @return SmsService
     */
    protected function getSmsService()
    {
        return $this->createService('Sms:SmsService');
    }

    private function getSmsNotificationClient()
    {
        return $this->biz['ESCloudSdk.sms'];
    }

    /**
     * @return \Biz\Notification\Service\NotificationService
     */
    protected function getNotificationService()
    {
        return $this->biz->service('Notification:NotificationService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }
}
