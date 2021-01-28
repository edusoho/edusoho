<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;

class LatestGroupThreadsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取小组最新话题.
     *
     * 可传入的参数：
     *
     *   count 必需 话题数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 最热话题
     */
    public function getData(array $arguments)
    {
        $conditions = array(
            'status' => 'open',
        );

        $threads = $this->getThreadService()->searchThreads($conditions, array('createdTime' => 'Desc'), 0, $arguments['count']);

        $userIds = ArrayToolkit::column($threads, 'userId');
        $userIds = array_merge($userIds, ArrayToolkit::column($threads, 'lastPostMemberId'));
        $users = $this->getUserService()->findUsersByIds($userIds);

        $groups = $this->getGroupService()->getGroupsByids(ArrayToolkit::column($threads, 'groupId'));

        foreach ($threads as &$thread) {
            $thread['user'] = isset($users[$thread['userId']]) ? $users[$thread['userId']] : null;
            $thread['lastPostMember'] = isset($users[$thread['lastPostMemberId']]) ? $users[$thread['lastPostMemberId']] : null;
            $thread['group'] = isset($groups[$thread['groupId']]) ? $groups[$thread['groupId']] : null;
            unset($thread);
        }

        return $threads;
    }

    private function getThreadService()
    {
        return $this->getServiceKernel()->getBiz()->service('Group:ThreadService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->getBiz()->service('User:UserService');
    }

    private function getGroupService()
    {
        return $this->getServiceKernel()->getBiz()->service('Group:GroupService');
    }
}
