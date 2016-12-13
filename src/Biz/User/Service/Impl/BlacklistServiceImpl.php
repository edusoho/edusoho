<?php
namespace Biz\User\Impl;

use Biz\BaseService;
use Biz\User\BlacklistService;
use Topxia\Common\ArrayToolkit;

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
            throw $this->createAccessDeniedException('您没有权限');
        }
        return $this->getBlacklistDao()->findByUserId($userId);
    }

    public function addBlacklist($blacklist)
    {
        if (!ArrayToolkit::requireds($blacklist, array('userId', 'blackId'))) {
            throw $this->createServiceException('缺少必要字段，添加黑名单失败！');
        }

        if (!$this->canTakeBlacklist($blacklist['userId'])) {
            throw $this->createAccessDeniedException('您没有权限');
        }

        $blackUser = $this->getUserService()->getUser($blacklist['blackId']);
        if (empty($blackUser)) {
            throw $this->createNotFoundException('被拉黑用户不存在');
        }

        $black = $this->getByUserIdAndBlackId($blacklist['userId'], $blackUser['id']);
        if (!empty($black)) {
            throw $this->createServiceException('不能重复添加黑名单！');
        }

        $blacklist['createdTime'] = time();

        return $this->getBlacklistDao()->create($blacklist);
    }

    public function deleteBlacklistByUserIdAndBlackId($userId, $blackId)
    {
        if (!$this->canTakeBlacklist($userId)) {
            throw $this->createAccessDeniedException('您没有权限');
        }
        $black = $this->getBlacklistByUserIdAndBlackId($userId, $blackId);
        if (empty($black)) {
            throw $this->createNotFoundException('该黑名单不存在');
        }

        return $this->getBlacklistDao()->deleteByUserIdAndBlackId($userId, $blackId);
    }

    public function canTakeBlacklist($userId)
    {
        $owner = $this->getUserService()->getUser($userId);
        if (empty($owner['id'])) {
            throw $this->createNotFoundException('黑名单拥有者用户不存在');
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

    protected function getBlacklistDao()
    {
        return $this->createDao('User:BlacklistDao');
    }
}
