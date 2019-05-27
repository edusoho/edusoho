<?php

namespace Biz\WeChat\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Biz\WeChat\Dao\UserWeChatDao;
use Biz\WeChat\Service\WeChatService;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class WeChatServiceImpl extends BaseService implements WeChatService
{
    public function getWeChatUser($id)
    {
        return $this->getUserWeChatDao()->get($id);
    }

    public function getWeChatUserByUserIdAndType($userId, $type)
    {
        return $this->getUserWeChatDao()->getByUserIdAndType($userId, $type);
    }

    public function findWeChatUsersByUserId($userId)
    {
        return $this->getUserWeChatDao()->findByUserId($userId);
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
        if (!ArrayToolkit::requireds($fields, array('appId', 'type', 'openId'))) {
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

    public function batchSyncOfficialWeChatUsers()
    {
        $biz = $this->biz;
        $weChatUsersList = $biz['wechat.template_message_client']->getUserList();
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
    }

    public function refreshOfficialWeChatUsers($lifeTime = 86400, $refreshNum = self::REFRESH_NUM)
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
            array('id', 'openId')
        );

        if (empty($weChatUsers)) {
            //这里加日志
            return;
        }
        $this->batchFreshOfficialWeChatUsers($weChatUsers);
    }

    public function batchFreshOfficialWeChatUsers($weChatUsers)
    {
        $biz = $this->biz;
        $userList = $this->convertWeChatUsersToOfficialRequestParams($weChatUsers);

        $freshWeChatUsers = $biz['wechat.template_message_client']->batchGetUserInfo($userList);
        $freshWeChatUsers = ArrayToolkit::index($freshWeChatUsers, 'openid');
        $fromIds = ArrayToolkit::column($freshWeChatUsers, 'unionid');

        $userBinds = $this->getUserService()->findUserBindByTypeAndFromIds('weixin', $fromIds);
        $userBinds = ArrayToolkit::index($userBinds, 'fromId');

        $batchUpdateHelper = new BatchUpdateHelper($this->getUserWeChatDao());
        foreach ($weChatUsers as $weChatUser) {
            $freshWeChatUser = isset($freshWeChatUsers[$weChatUser['openId']]) ? $freshWeChatUsers[$weChatUser['openId']] : array();
            $unionId = !empty($freshWeChatUser['unionid']) ? $freshWeChatUser['unionid'] : null;
            $userId = !empty($unionId) && !empty($userBinds[$unionId]) ? $userBinds[$unionId]['toId'] : 0;
            $updateField = array(
                'unionId' => $unionId,
                'userId' => $userId,
                'isSubscribe' => $freshWeChatUser['subscribe'],
                'data' => $freshWeChatUser,
                'lastRefreshTime' => time(),
            );
            $batchUpdateHelper->add('id', $weChatUser['id'], $updateField);
        }

        $batchUpdateHelper->flush();
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
}
