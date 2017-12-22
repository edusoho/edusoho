<?php

namespace Biz\User\Service\Impl;

use Biz\BaseService;
use Biz\User\Service\UserActiveService;
use Biz\User\Dao\Impl\UserActiveDaoImpl;
use Symfony\Component\Filesystem\Filesystem;

class UserActiveServiceImpl extends BaseService implements UserActiveService
{
    public function createActiveUser($userId = null)
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

        return $this->getActiveUserDao()->create($activeUserLog);
    }

    public function getActiveUser($userId)
    {
        return $this->getActiveUserDao()->getByUserId($userId);
    }

    public function isActiveUser($userId = null)
    {
        if (empty($userId)) {
            $user = $this->getCurrentUser();
            $userId = $user->getId();
        }
        $activeUser = $this->getActiveUser($userId);

        return !empty($activeUser);
    }

    public function analysisActiveUser($startTime, $endTime)
    {
        return $this->getActiveUserDao()->analysis($startTime, $endTime);
    }

    public function writeToFile($path, $activeUser)
    {
        $fileSystem = new Filesystem();
        if (!file_exists(dirname($path))) {
            $fileSystem->mkdir(dirname($path));
        }
        file_put_contents($path, json_encode($activeUser));

        return true;
    }

    /**
     * @return UserActiveDaoImpl
     */
    public function getActiveUserDao()
    {
        return $this->createDao('User:UserActiveDao');
    }
}
