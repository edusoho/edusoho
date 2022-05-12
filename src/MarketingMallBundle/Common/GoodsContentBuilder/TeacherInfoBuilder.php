<?php

namespace MarketingMallBundle\Common\GoodsContentBuilder;

use Biz\User\Service\UserService;

class TeacherInfoBuilder extends AbstractBuilder
{
    public function build($id)
    {
        $user = $this->getUserService()->getUserAndProfile($id);

        return [
            'user_id' => $id,
            'name' => $user['nickname'],
            'title' => $user['title'],
            'cover' => $user['smallAvatar'],
            'about' => $user['about'],
        ];
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
