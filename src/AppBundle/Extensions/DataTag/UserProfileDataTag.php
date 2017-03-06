<?php

namespace AppBundle\Extensions\DataTag;

class UserProfileDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取一个用户的个人详细信息.
     *
     * 可传入的参数：
     *   userId 必需 用户ID
     *
     * @param array $arguments 参数
     *
     * @return array 用户个人信息
     */
    public function getData(array $arguments)
    {
        $this->checkUserId($arguments);

        $userProfile = $this->getUserService()->getUserProfile($arguments['userId']);

        return $userProfile;
    }
}
