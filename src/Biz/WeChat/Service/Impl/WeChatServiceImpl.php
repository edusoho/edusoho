<?php

namespace Biz\WeChat\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Biz\WeChat\Dao\UserWeChatDao;
use Biz\WeChat\Service\WeChatService;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Biz\CloudPlatform\CloudAPIFactory;
use QiQiuYun\SDK\Constants\NotificationChannelTypes;

class WeChatServiceImpl extends BaseService implements WeChatService
{
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

    //@TODO 不用批量接口
    public function getOfficialWeChatUserByUserId($userId)
    {
        $weChatUser = $this->getUserWeChatDao()->getByUserIdAndType($userId, self::OFFICIAL_TYPE);
        if (empty($weChatUser)) {
            return array();
        }

        if ($weChatUser['lastRefreshTime'] < time() - self::FRESH_TIME) {
            $this->batchFreshOfficialWeChatUsers(array($weChatUser));
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

    public function searchWeChatUsers($conditions, $orderBys, $start, $limit, $columns = array())
    {
        return $this->getUserWeChatDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function batchSyncOfficialWeChatUsers($nextOpenId = '')
    {
        $biz = $this->biz;
        $weChatUsersList = $biz['wechat.template_message_client']->getUserList($nextOpenId);
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
            'type' => self::OFFICIAL_TYPE,
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
            //这里加日志
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

    public function batchFreshOfficialWeChatUsers($weChatUsers)
    {
        $biz = $this->biz;
        $userList = $this->convertWeChatUsersToOfficialRequestParams($weChatUsers);

        $freshWeChatUsers = $biz['wechat.template_message_client']->batchGetUserInfo($userList);
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
            );
            $batchUpdateHelper->add('id', $weChatUser['id'], $updateField);
        }

        $batchUpdateHelper->flush();
    }

    public function getTemplateId($key)
    {
        $wechatSetting = $this->getSettingService()->get('wechat', array());
        if (empty($wechatSetting['wechat_notification_enabled'])) {
            return;
        }

        if (empty($wechatSetting[$key]['status']) || empty($wechatSetting[$key]['templateId'])) {
            return;
        }

        return $wechatSetting[$key]['templateId'];
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
}
