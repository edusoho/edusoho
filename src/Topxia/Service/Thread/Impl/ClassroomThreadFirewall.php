<?php
namespace Topxia\Service\Thread\Impl;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Thread\AbstractThreadFirewall;

class ClassroomThreadFirewall extends AbstractThreadFirewall
{
    public function accessThreadRead($thread)
    {
        return $this->getClassroomService()->canLookClassroom($thread['targetId']);
    }

    public function accessThreadCreate($thread)
    {
        return $this->getClassroomService()->canLookClassroom($thread['targetId']);
    }

    public function accessThreadDelete($thread)
    {
        return $this->hasManagePermission($thread, true);
    }

    public function accessThreadUpdate($thread)
    {
        return $this->hasManagePermission($thread, true);
    }

    public function accessThreadSticky($thread)
    {
        return $this->hasManagePermission($thread, false);
    }

    public function accessThreadNice($thread)
    {
        return $this->hasManagePermission($thread, false);
    }

    public function accessThreadSolved($thread)
    {
        return $this->hasManagePermission($thread, false);
    }

    public function accessPostCreate($post)
    {
        return $this->getClassroomService()->canLookClassroom($post['targetId']);
    }

    public function accessPostUpdate($post)
    {
        return $this->hasManagePermission($post, true);
    }

    public function accessPostDelete($post)
    {
        return $this->hasManagePermission($post, true);
    }

    public function accessPostVote($post)
    {
        return $this->getClassroomService()->canLookClassroom($post['targetId']);
    }

    public function accessPostAdopted($post)
    {
        $result = $this->getClassroomService()->canLookClassroom($post['targetId']);
        return $result;
    }

    public function accessEventCreate($resource)
    {
        return $this->getClassroomService()->canCreateThreadEvent($resource);
    }

    public function accessMemberDelete($member)
    {
        if ($this->getClassroomService()->canManageClassroom($member['targetId'])) {
            return true;
        }

        $user = $this->getCurrentUser();

        if ($member['userId'] == $user['id']) {
            return true;
        }

        return false;
    }

    protected function hasManagePermission($resource, $ownerCanManage = false)
    {
        if ($this->getClassroomService()->canManageClassroom($resource['targetId'])) {
            return true;
        }

        $user = $this->getCurrentUser();

        if ($ownerCanManage && ($resource['userId'] == $user['id'])) {
            return true;
        }

        return false;
    }

    protected function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
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
