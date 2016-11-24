<?php
/**
 * User: Edusoho V8
 * Date: 19/10/2016
 * Time: 19:02
 */

namespace Topxia\Service\User\Impl;


use Topxia\Service\Common\BaseService;
use Topxia\Service\User\Dao\Impl\UserActiveDaoImpl;
use Topxia\Service\User\Dao\Impl\UserActiveLogDaoImpl;
use Topxia\Service\User\UserActiveService;
use Symfony\Component\Filesystem\Filesystem;

class UserActiveServiceImpl extends BaseService implements UserActiveService
{
    public function createActiveUser($userId = null)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isLogin()) {
            return false;
        }
        if (empty($userId)) {
            $userId = $currentUser->getId();
        }
        $activeUserLog = array();

        $activeUserLog['userId']     = $userId;
        $activeUserLog['activeTime'] = date('Ymd', time());

        $this->getActiveUserDao()->createActiveUser($activeUserLog);
    }

    public function getActiveUser($userId)
    {
        return $this->getActiveUserDao()->getActiveUser($userId);
    }

    public function isActiveUser($userId = null)
    {
        if (empty($userId)) {
            $user   = $this->getCurrentUser();
            $userId = $user->getId();
        }
        $activeUser = $this->getActiveUser($userId);
        return !empty($activeUser);
    }

    public function analysisActiveUser($startTime, $endTime)
    {
        return $this->getActiveUserDao()->analysisActiveUser($startTime, $endTime);
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

    public function getFilePath($userId)
    {
        $rootDir = realpath($this->getKernel()->getParameter('kernel.root_dir').'/../');
        return $rootDir."/app/data/active_user/{$userId}/".date('Y_m_d', time());
    }


    /**
     * @return UserActiveDaoImpl
     */
    public function getActiveUserDao()
    {
        return $this->createDao('User.UserActiveDao');
    }


}