<?php

namespace MarketingMallBundle\Common\GoodsContentBuilder;

use ApiBundle\Api\Util\AssetHelper;
use Biz\User\Service\UserService;

class TeacherInfoBuilder extends AbstractBuilder
{
    public function build($id)
    {
        $user = $this->getUserService()->getUserAndProfile($id);
        file_put_contents('/tmp/1.txt', json_encode($user));

        return [
            'userId' => $id,
            'name' => $user['nickname'],
            'title' => $user['title'],
            'verifiedMobile' => $user['verifiedMobile'],
            'cover' => AssetHelper::getFurl($user['largeAvatar'], 'user_avatar.png'),
            'about' => $this->transformImages($user['about']),
        ];
    }

    public function builds($ids)
    {

    }
    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
