<?php
namespace Topxia\Service\Thread\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Thread\ThreadService;
use Topxia\Common\ArrayToolkit;

class ClassroomThreadFirewall extends AbstractThreadFirewall
{

    public function accessThreadRead($thread)
    {

    }

    public function accessThreadDelete($thread)
    {
        return $this->hasManagePermission($thread, true);
    }

    public function accessThreadUpdate($thread)
    {
        return $this->hasManagePermission($thread, true);
    }

    public function accessThreadStick($thread)
    {
        return $this->hasManagePermission($thread, false);
    }

    public function accessThreadNice($thread)
    {
        return $this->hasManagePermission($thread, false);
    }

    public function accessPostCreate($post)
    {

    }

    public function accessPostUpdate($post)
    {
        return $this->hasManagePermission($thread, true);
    }

    public function accessPostDelete($post)
    {
        return $this->hasManagePermission($thread, true);
    }

    private function hasCreatePermission($resource)
    {
        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            return true;
        }

        if ($this->getClassroomService()->isClassroomManager($resource['targetId'], $user['id'])) {
            return true;
        }
    }

    private function hasManagePermission($resource, $ownerCanManage = false)
    {
        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            return true;
        }

        if ($this->getClassroomService()->isClassroomManager($resource['targetId'], $user['id'])) {
            return true;
        }

        if ($ownerCanManage && ($resource['userId'] == $user['id'];)) {
            return true;
        }

        return false;
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }

    public function getCurrentUser()
    {
        return $this->getKernel()->getCurrentUser();
    }

}