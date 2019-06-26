<?php

namespace Biz\User\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\User\BlacklistException;
use Biz\User\Dao\BlacklistDao;
use AppBundle\Common\ArrayToolkit;
use Biz\User\Service\BlacklistService;
use Biz\User\UserException;

class BlacklistServiceImpl extends BaseService implements BlacklistService
{
    public function getBlacklist($id)
    {
        return $this->getBlacklistDao()->get($id);
    }

    public function getBlacklistByUserIdAndBlackId($userId, $blackId)
    {
        return $this->getBlacklistDao()->getByUserIdAndBlackId($userId, $blackId);
    }

    public function findBlacklistsByUserId($userId)
    {
        if (!$this->canTakeBlacklist($userId)) {
            $this->createNewException(BlacklistException::FORBIDDEN_TAKE_BLACKLIST());
        }

        return $this->getBlacklistDao()->findByUserId($userId);
    }

    public function addBlacklist($blacklist)
    {
        if (!ArrayToolkit::requireds($blacklist, array('userId', 'blackId'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (!$this->canTakeBlacklist($blacklist['userId'])) {
            $this->createNewException(BlacklistException::FORBIDDEN_TAKE_BLACKLIST());
        }

        $blackUser = $this->getUserService()->getUser($blacklist['blackId']);
        if (empty($blackUser)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $black = $this->getBlacklistDao()->getByUserIdAndBlackId($blacklist['userId'], $blackUser['id']);
        if (!empty($black)) {
            $this->createNewException(BlacklistException::DUPLICATE_ADD());
        }

        $blacklist['createdTime'] = time();

        return $this->getBlacklistDao()->create($blacklist);
    }

    public function deleteBlacklistByUserIdAndBlackId($userId, $blackId)
    {
        if (!$this->canTakeBlacklist($userId)) {
            $this->createNewException(BlacklistException::FORBIDDEN_TAKE_BLACKLIST());
        }
        $black = $this->getBlacklistByUserIdAndBlackId($userId, $blackId);
        if (empty($black)) {
            $this->createNewException(BlacklistException::NOTFOUND_BLACKLIST());
        }

        return $this->getBlacklistDao()->deleteByUserIdAndBlackId($userId, $blackId);
    }

    public function canTakeBlacklist($userId)
    {
        $owner = $this->getUserService()->getUser($userId);
        if (empty($owner['id'])) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }
        $user = $this->getCurrentUser();

        if ($user['id'] == $userId || $user->isAdmin()) {
            return true;
        }

        return false;
    }

    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return BlacklistDao
     */
    protected function getBlacklistDao()
    {
        return $this->createDao('User:BlacklistDao');
    }
}
