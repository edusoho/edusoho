<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use Biz\Group\Service\ThreadService;

class HotThreadsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取最热话题.
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
        $groupSetting = $this->getSettingService()->get('group', array());
        $timeRange = 7 * 24 * 60 * 60;
        if (isset($groupSetting['threadTime_range'])) {
            $timeRange = $groupSetting['threadTime_range'] * 24 * 60 * 60;
        }

        $hotThreads = $this->getThreadService()->searchThreads(
            array(
                'createdTime' => time() - $timeRange,
                'status' => 'open',
            ),
            array(
                'isStick' => 'DESC',
                'postNum' => 'DESC',
                'createdTime' => 'DESC',
            ),
            0,
            $arguments['count']
        );

        $ownerIds = ArrayToolkit::column($hotThreads, 'userId');
        $groupIds = ArrayToolkit::column($hotThreads, 'groupId');
        $userIds = ArrayToolkit::column($hotThreads, 'lastPostMemberId');

        $lastPostMembers = $this->getUserService()->findUsersByIds($userIds);

        $owners = $this->getUserService()->findUsersByIds($ownerIds);

        $groups = $this->getGroupService()->getGroupsByids($groupIds);

        foreach ($hotThreads as $key => $thread) {
            if ($thread['userId'] == $owners[$thread['userId']]['id']) {
                $hotThreads[$key]['user'] = $owners[$thread['userId']];
            }

            if ($thread['lastPostMemberId'] > 0 && $thread['lastPostMemberId'] == $lastPostMembers[$thread['lastPostMemberId']]['id']) {
                $hotThreads[$key]['lastPostMember'] = $lastPostMembers[$thread['lastPostMemberId']];
            }

            if ($thread['groupId'] == $groups[$thread['groupId']]['id']) {
                $hotThreads[$key]['group'] = $groups[$thread['groupId']];
            }
        }

        return $hotThreads;
    }

    /**
     * @return ThreadService
     */
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

    protected function getSettingService()
    {
        return $this->getServiceKernel()->getBiz()->service('System:SettingService');
    }
}
