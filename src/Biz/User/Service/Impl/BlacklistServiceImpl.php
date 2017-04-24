<?php

namespace Biz\User\Service\Impl;

use Biz\BaseService;
use Biz\User\Dao\BlacklistDao;
use AppBundle\Common\ArrayToolkit;
use Biz\User\Service\BlacklistService;

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
            throw $this->createAccessDeniedException('Access Denied');
        }

        return $this->getBlacklistDao()->findByUserId($userId);
    }

    public function addBlacklist($blacklist)
    {
        if (!ArrayToolkit::requireds($blacklist, array('userId', 'blackId'))) {
            throw $this->createInvalidArgumentException('userId and blackId required');
        }

        if (!$this->canTakeBlacklist($blacklist['userId'])) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        $blackUser = $this->getUserService()->getUser($blacklist['blackId']);
        if (empty($blackUser)) {
            throw $this->createNotFoundException('User Not Found');
        }

        $black = $this->getByUserIdAndBlackId($blacklist['userId'], $blackUser['id']);
        if (!empty($black)) {
            throw $this->createAccessDeniedException('User has been added');
        }

        $blacklist['createdTime'] = time();

        return $this->getBlacklistDao()->create($blacklist);
    }

    public function deleteBlacklistByUserIdAndBlackId($userId, $blackId)
    {
        if (!$this->canTakeBlacklist($userId)) {
            throw $this->createAccessDeniedException('Access Denied');
        }
        $black = $this->getBlacklistByUserIdAndBlackId($userId, $blackId);
        if (empty($black)) {
            throw $this->createNotFoundException('Blacklist Not Found');
        }

        return $this->getBlacklistDao()->deleteByUserIdAndBlackId($userId, $blackId);
    }

    public function canTakeBlacklist($userId)
    {
        $owner = $this->getUserService()->getUser($userId);
        if (empty($owner['id'])) {
            throw $this->createNotFoundException('Blacklist Owner Not Found');
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
