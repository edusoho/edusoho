<?php

namespace ApiBundle\Api\Resource\ImUser;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;

class ImUser extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\User\UserFilter", mode="simple")
     */
    public function search(ApiRequest $request)
    {
        $keyword = $request->query->get('keyword');
        if (empty($keyword)) {
            return [];
        }

        $mobileProfiles = $this->getUserService()->searchUserProfiles(['mobile' => $keyword], ['id' => 'DESC'], 0, 5);
        $qqProfiles = $this->getUserService()->searchUserProfiles(['qq' => $keyword], ['id' => 'DESC'], 0, 5);
        $mobileAndQQUserIds = array_merge(
            ArrayToolkit::column($mobileProfiles, 'id'),
            ArrayToolkit::column($qqProfiles, 'id')
        );
        $mobileAndQQUsers = $this->getUserService()->findUsersByIds($mobileAndQQUserIds);

        $nicknameUsers = ArrayToolkit::index(
            $this->getUserService()->searchUsers(['nickname' => $keyword], ['nickname' => 'ASC'], 0, 5),
            'id'
        );

        $users = ArrayToolkit::mergeArraysValue([$mobileAndQQUsers, $nicknameUsers]);
        foreach ($users as $key => $user) {
            if (1 == $user['destroyed']) {
                unset($users[$key]);
            }
        }

        return array_values($users);
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}
