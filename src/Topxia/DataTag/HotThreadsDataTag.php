<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;
use Topxia\Common\ArrayToolkit;

class HotThreadsDataTag extends BaseDataTag implements DataTag  
{
    public function getData(array $arguments)
    {
        $hotThreads = $this->getThreadService()->searchThreads(
            array(
                'createdTime'=>time()-14*24*60*60,
                'status'=>'open'
                ),
            array(
                    array('isStick','DESC'),
                    array('postNum','DESC'),
                    array('createdTime','DESC'),
                ),0, $arguments['count']
        );

        $ownerIds = ArrayToolkit::column($hotThreads, 'userId');
        $groupIds = ArrayToolkit::column($hotThreads, 'groupId');
        $userIds =  ArrayToolkit::column($hotThreads, 'lastPostMemberId');

        $lastPostMembers=$this->getUserService()->findUsersByIds($userIds);

        $owners=$this->getUserService()->findUsersByIds($ownerIds);

        $groups=$this->getGroupService()->getGroupsByids($groupIds);


        foreach ($hotThreads as $key => $thread) {

            if ($thread['userId'] == $owners[$thread['userId']]['id'] ) {
                $hotThreads[$key]['user'] = $owners[$thread['userId']];
            }

            if ($thread['lastPostMemberId']>0 && $thread['lastPostMemberId'] == $lastPostMembers[$thread['lastPostMemberId']]['id'] ) {
                $hotThreads[$key]['lastPostMember'] = $lastPostMembers[$thread['lastPostMemberId']];
            }

            if ($thread['groupId'] == $groups[$thread['groupId']]['id'] ) {
                $hotThreads[$key]['group'] = $groups[$thread['groupId']];
            }
        }
        return $hotThreads;
    }

    private function getThreadService()
    {
        return $this->getServiceKernel()->createService('Group.ThreadService');
    }
    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
    private function getGroupService() 
    {
        return $this->getServiceKernel()->createService('Group.GroupService');
    }

}
