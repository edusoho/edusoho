<?php

namespace MarketingMallBundle\Common\GoodsContentBuilder;

use ApiBundle\Api\Util\AssetHelper;
use Biz\User\Service\UserService;

class TeacherInfoBuilder extends AbstractBuilder
{
    public function build($id)
    {
        $user = $this->getUserService()->getUserAndProfile($id);
        file_put_contents('1.txt', json_encode($user));

        return [
            'userId' => $id,
            'name' => $user['nickname'],
            'title' => $user['title'],
            'cover' => AssetHelper::getFurl($user['smallAvatar'], 'user_avatar.png'),
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
