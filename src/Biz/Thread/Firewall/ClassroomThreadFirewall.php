<?php

namespace Biz\Thread\Firewall;

use Topxia\Service\Common\ServiceKernel;

class ClassroomThreadFirewall extends AbstractThreadFirewall
{
    public function accessThreadRead($thread)
    {
        return $this->getClassroomService()->canLookClassroom($thread['targetId']);
    }

    public function accessThreadCreate($thread)
    {
        $user = $this->getCurrentUser();
        $member = $this->getClassroomService()->getClassroomMember($thread['targetId'], $user['id']);

        $classroom = $this->getClassroomService()->getClassroom($thread['targetId']);

        if (!empty($member['levelId'])
            && empty($classroom['vipLevelId'])) {
            return false;
        }

        if ($this->isVipPluginEnabled()
            && $this->getSettingService()->get('vip.enabled', 0)
            && !empty($member['levelId'])
            && $this->getVipService()->checkUserInMemberLevel($user['id'], $classroom['vipLevelId']) != 'ok') {
            return false;
        }

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
        if ($this->getClassroomService()->canManageClassroom($resource['targetId'], 'admin_course_thread')) {
            return true;
        }

        $user = $this->getCurrentUser();

        if ($ownerCanManage && $resource['userId'] == $user['id']) {
            return true;
        }

        return false;
    }

    protected function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:ClassroomService');
    }

    protected function getVipService()
    {
        return ServiceKernel::instance()->createService('VipPlugin:Vip:VipService');
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }

    protected function isVipPluginEnabled()
    {
        $setting = $this->getSettingService()->get('vip');

        return $this->isPluginInstalled('Vip') && !empty($setting['enabled']);
    }

    protected function isPluginInstalled($code)
    {
        $app = ServiceKernel::instance()->createService('CloudPlatform:AppService')->getAppByCode($code);

        return !empty($app);
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    public function getCurrentUser()
    {
        return $this->getKernel()->getCurrentUser();
    }
}
