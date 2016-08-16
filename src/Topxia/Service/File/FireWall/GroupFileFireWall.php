<?php

namespace Topxia\Service\FIle\FireWall;

use Topxia\Service\Common\ServiceKernel;

class GroupFileFireWall
{
    public function canAccess($attachment)
    {
        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            return true;
        }

        $targetTypes = explode('.', $attachment['targetType']);
        $type        = array_pop($targetTypes);
        if ($type === 'thread') {
            $thread = $this->getThreadService()->getThread($attachment['targetId']);
            $group  = $this->getGroupService()->getGroup($thread['groupId']);

            if ($user['id'] == $thread['userId'] || $user['id'] == $group['ownerId']) {
                return true;
            }
        } elseif ($type === 'post') {
            $post   = $this->getThreadService()->getPost($attachment['targetId']);
            $thread = $this->getThreadService()->getThread($post['threadId']);
            $group  = $this->getGroupService()->getGroup($thread['groupId']);
            if ($user['id'] == $post['userId'] || $user['id'] == $thread['userId'] || $user['id'] == $group['ownerId']) {
                return true;
            }
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

    protected function getThreadService()
    {
        return $this->getKernel()->createService('Group.ThreadService');
    }

    protected function getGroupService()
    {
        return $this->getKernel()->createService('Group.GroupService');
    }
}
