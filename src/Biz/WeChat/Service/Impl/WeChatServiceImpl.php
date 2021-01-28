<?php

namespace Biz\WeChat\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Biz\WeChat\Dao\UserWeChatDao;
use Biz\WeChat\Service\WeChatService;
use Biz\System\Service\SettingService;
use Biz\WeChat\WeChatException;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Biz\CloudPlatform\CloudAPIFactory;
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
        $preAuthUrl = $this->biz['qiQiuYunSdk.wechat']->getPreAuthUrl($platformType, $callbackUrl);

        return $preAuthUrl['url'];
    }

    public function saveWeChatTemplateSetting($key, $fields)
    {
        $wechatSetting = $this->getSettingService()->get('wechat', array());
        if (!ArrayToolkit::requireds($fields, array('status'))) {
            throw new InvalidArgumentException('缺少必要字段');
        }

        $fields['scenes'] = empty($fields['scenes']) ? array() : $fields['scenes'];
        $wechatSetting['templates'][$key] = empty($wechatSetting['templates'][$key]) ? $fields : array_merge($wechatSetting['templates'][$key], $fields);
        $this->getSettingService()->set('wechat', $wechatSetting);
        $this->dispatchEvent('wechat.template_setting.save', new Event($fields, array('key' => $key, 'wechatSetting' => $wechatSetting)));

        return true;
    }

    public function getWeChatSendChannel()
    {
        $wechatSetting = $this->getSettingService()->get('wechat', array());

        return empty($wechatSetting['is_authorization']) ? 'wechat' : 'wechat_agent';
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
            return array();
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
        if (!ArrayToolkit::requireds($fields, array('appId', 'type'))) {
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

    public function searchWeChatUsers($conditions, $orderBys, $start, $limit, $columns = array())
    {
        return $this->getUserWeChatDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function batchSyncOfficialWeChatUsers($nextOpenId = '')
    {
        $biz = $this->biz;
        $weChatSetting = $this->getSettingService()->get('wechat', array());
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

            $saveData = array();
            $appId = $biz['wechat.template_message_client']->getAppId();
            foreach ($unExistOpenIds as $openId) {
                $saveData[] = array(
                    'appId' => $appId,
                    'type' => self::OFFICIAL_TYPE,
                    'openId' => $openId,
                );
            }

            if (!empty($saveData)) {
                $this->getUserWeChatDao()->batchCreate($saveData);
            }
        }

        return array(
            'next_openid' => $weChatUsersList['next_openid'],
        );
    }

    public function refreshOfficialWeChatUsers($lifeTime = WeChatService::FRESH_TIME, $refreshNum = self::REFRESH_NUM)
    {
        $conditions = array(
            'type' => WeChatService::OFFICIAL_TYPE,
            'lastRefreshTime_LT' => time() - $lifeTime,
        );
        $weChatUsers = $this->searchWeChatUsers(
            $conditions,
            array('lastRefreshTime' => 'ASC'),
            0,
            $refreshNum,
            array('id', 'openId', 'unionId', 'userId')
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
                $this->createWeChatUser(array(
                    'type' => WeChatService::OFFICIAL_TYPE,
                    'appId' => $this->getSettingService()->node('login_bind.weixinmob_key', ''),
                    'unionId' => $bind['fromId'],
                    'openId' => $token['openid'],
                    'userId' => $user['id'],
                ));
            } elseif ($weChatUser['id'] != $user['id']) {
                $this->updateWeChatUser($weChatUser['id'], array(
                    'userId' => $user['id'],
                ));
            }
        } catch (\Exception $e) {
            $this->getLogger()->error('WeChatFreshOfficialUser_'.$e->getMessage(), $e->getTrace());
        }
    }

    public function freshOfficialWeChatUser($weChatUser)
    {
        $biz = $this->biz;
        $wechatSetting = $this->getSettingService()->get('wechat', array());
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

        $updateField = array(
            'unionId' => $unionId,
            'userId' => $userId,
            'data' => $freshWeChatUser,
            'isSubscribe' => empty($freshWeChatUser['subscribe']) ? 0 : $freshWeChatUser['subscribe'],
            'lastRefreshTime' => time(),
            'nickname' => empty($freshWeChatUser['nickname']) ? '' : urlencode($freshWeChatUser['nickname']),
            'profilePicture' => empty($freshWeChatUser['headimgurl']) ? '' : $freshWeChatUser['headimgurl'],
            'subscribeTime' => empty($freshWeChatUser['subscribe_time']) ? 0 : $freshWeChatUser['subscribe_time'],
        );

        $this->updateWeChatUser($weChatUser['id'], $updateField);
    }

    public function batchFreshOfficialWeChatUsers($weChatUsers)
    {
        $biz = $this->biz;
        $wechatSetting = $this->getSettingService()->get('wechat', array());
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
            $freshWeChatUser = isset($freshWeChatUsers[$weChatUser['openId']]) ? $freshWeChatUsers[$weChatUser['openId']] : array();

            $unionId = !empty($freshWeChatUser['unionid']) ? $freshWeChatUser['unionid'] : $weChatUser['unionId'];

            $userId = !empty($unionId) && !empty($userBinds[$unionId]) ? $userBinds[$unionId]['toId'] : 0;
            $updateField = array(
                'unionId' => $unionId,
                'userId' => $userId,
                'data' => $freshWeChatUser,
                'isSubscribe' => empty($freshWeChatUser['subscribe']) ? 0 : $freshWeChatUser['subscribe'],
                'lastRefreshTime' => time(),
                'nickname' => empty($freshWeChatUser['nickname']) ? '' : urlencode($freshWeChatUser['nickname']),
                'profilePicture' => empty($freshWeChatUser['headimgurl']) ? '' : $freshWeChatUser['headimgurl'],
                'subscribeTime' => empty($freshWeChatUser['subscribe_time']) ? 0 : $freshWeChatUser['subscribe_time'],
            );
            $batchUpdateHelper->add('id', $weChatUser['id'], $updateField);
        }

        $batchUpdateHelper->flush();
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
        $wechatSetting = $this->getSettingService()->get('wechat', array());
        if (empty($wechatSetting['wechat_notification_enabled'])) {
            return null;
        }

        $template = !empty($wechatSetting['templates'][$key]) ? $wechatSetting['templates'][$key] : array();

        if (empty($template['status']) || empty($template['templateId'])) {
            return null;
        }

        $scenes = empty($template['scenes']) ? array() : $template['scenes'];
        if (!empty($scene) && !in_array($scene, $scenes)) {
            return null;
        }

        return $template['templateId'];
    }

    /**
     * @param $template
     * @param $key
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function addTemplate($template, $key)
    {
        $client = $this->getTemplateClient();
        if (empty($client)) {
            $this->createNewException(WeChatException::TOKEN_MAKE_ERROR());
        }

        $wechatSetting = $this->getSettingService()->get('wechat');
        if (empty($wechatSetting['templates'][$key]['templateId'])) {
            try {
                if (!empty($wechatSetting['is_authorization'])) {
                    $data = $this->getSDKWeChatService()->createNotificationTemplate($template['id']);
                } else {
                    $data = $client->addTemplate($template['id']);
                }
            } catch (\Exception $e) {
                if ($e->getCode() == 40220005) {
                    $this->createNewException(WeChatException::TEMPLATE_EXCEEDS_LIMIT());
                }

                if ($e->getCode() == 40220007) {
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
        $this->getSettingService()->set('wechat', $wechatSetting);

        return $this->getSettingService()->get('wechat', $wechatSetting);
    }

    /**
     * @param $template
     * @param $key
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function deleteTemplate($template, $key)
    {
        $client = $this->getTemplateClient();
        if (empty($client)) {
            $this->createNewException(WeChatException::TOKEN_MAKE_ERROR());
        }

        $wechatSetting = $this->getSettingService()->get('wechat');

        if (!empty($wechatSetting['templates'][$key]['templateId'])) {
            if (!empty($wechatSetting['is_authorization'])) {
                $data = $this->getSDKWeChatService()->deleteNotificationTemplate($wechatSetting['templates'][$key]['templateId']);
            } else {
                $data = $client->deleteTemplate($wechatSetting['templates'][$key]['templateId']);
            }

            if (empty($data)) {
                $this->createNewException(WeChatException::TEMPLATE_OPEN_ERROR());
            }
        }

        $wechatSetting['templates'][$key]['templateId'] = '';
        $wechatSetting['templates'][$key]['status'] = 0;

        return $this->getSettingService()->set('wechat', $wechatSetting);
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
                $biz['qiQiuYunSdk.notification']->openAccount();
                $result = $biz['qiQiuYunSdk.notification']->openChannel(NotificationChannelTypes::WECHAT, array(
                    'app_id' => $loginConnect['weixinmob_key'],
                    'app_secret' => $loginConnect['weixinmob_secret'],
                ));
                $this->registerJobs();
            } else {
                $biz['qiQiuYunSdk.notification']->closeAccount();
                $result = $biz['qiQiuYunSdk.notification']->closeChannel(NotificationChannelTypes::WECHAT);
                $this->deleteJobs();
            }
        } catch (\RuntimeException $e) {
            return false;
        }

        if (empty($result)) {
            return false;
        }

        return true;
    }

    public function getJobs()
    {
        $jobs = array(
            array(
                'name' => 'WeChatUsersSyncJob',
                'expression' => '*/60 * * * *',
                'class' => 'Biz\WeChat\Job\WeChatUsersSync',
                'misfire_threshold' => 60 * 10,
            ),
            array(
                'name' => 'WeChatUserFreshJob',
                'expression' => '*/5 * * * *',
                'class' => 'Biz\WeChat\Job\WeChatUserFreshJob',
                'misfire_threshold' => 60 * 5,
            ),
        );

        return $jobs;
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
        $userList = array();

        foreach ($weChatUsers as $user) {
            $userList[] = array(
                'openid' => $user['openId'],
                'lang' => $lang,
            );
        }

        return $userList;
    }

    private function getTemplateClient()
    {
        return $this->biz['wechat.template_message_client'];
    }

    private function weChatUserFilter($fields)
    {
        return ArrayToolkit::parts($fields, array('appId', 'type', 'userId', 'openId', 'unionId', 'data', 'lastRefreshTime'));
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
        return $this->biz['qiQiuYunSdk.wechat'];
    }
}
