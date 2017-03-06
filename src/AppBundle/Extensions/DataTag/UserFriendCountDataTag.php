<?php

namespace AppBundle\Extensions\DataTag;

use Topxia\Service\Common\ServiceKernel;

class UserFriendCountDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取一个用户的关注/粉丝的数量.
     *
     * 可传入的参数：
     *   userId 必需 用户ID
     *
     * @param array $arguments 参数
     *
     * @return array 一个用户的关注/粉丝的数量
     */
    public function getData(array $arguments)
    {
        $result = array();

        // 关注数
        $result['following'] = $this->getUserService()->findUserFollowingCount($arguments['userId']);
        // 粉丝数
        $result['follower'] = $this->getUserService()->findUserFollowerCount($arguments['userId']);

        return $result;
    }

    private function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}
