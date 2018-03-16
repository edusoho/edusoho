<?php

namespace Biz\User\Service\Impl;

use Biz\BaseService;
use Biz\User\Service\UserActiveService;
use Biz\User\Dao\Impl\UserActiveDaoImpl;

class UserActiveServiceImpl extends BaseService implements UserActiveService
{
    public function analysisActiveUser($startTime, $endTime)
    {
        return $this->getActiveUserDao()->analysis($startTime, $endTime);
    }

    public function saveOnline($onLine)
    {
        $this->getOnlineService()->saveOnline($onLine);

        if ($onLine['user_id'] > 0 && !$this->isActiveUser($onLine['user_id'])) {
            $this->createActiveUser($onLine['user_id']);
        }
    }

    private function createActiveUser($userId = null)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isLogin()) {
            return array();
        }
        if (empty($userId)) {
            $userId = $currentUser->getId();
        }
        $activeUserLog = array();

        $activeUserLog['userId'] = $userId;
        $activeUserLog['activeTime'] = date('Ymd', time());
        $record = $this->getActiveUserDao()->create($activeUserLog);

        $this->dispatch('user.daily.active', $record);

        return $record;
    }

    private function isActiveUser($userId = null)
    {
        if (empty($userId)) {
            $user = $this->getCurrentUser();
            $userId = $user->getId();
        }
        $activeUser = $this->getActiveUserDao()->getByUserId($userId);

        return !empty($activeUser);
    }

    /**
     * @return UserActiveDaoImpl
     */
    public function getActiveUserDao()
    {
        return $this->createDao('User:UserActiveDao');
    }

    /**
     * @return \Codeages\Biz\Framework\Session\Service\OnlineService
     */
    private function getOnlineService()
    {
        return $this->biz->service('Session:OnlineService');
    }
}
