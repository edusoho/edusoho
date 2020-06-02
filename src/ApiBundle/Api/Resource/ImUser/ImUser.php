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
            return array();
        }

        $mobileProfiles = $this->getUserService()->searchUserProfiles(array('mobile' => $keyword), array('id' => 'DESC'), 0, 5);
        $qqProfiles = $this->getUserService()->searchUserProfiles(array('qq' => $keyword), array('id' => 'DESC'), 0, 5);
        $mobileAndQQUserIds = array_merge(
            ArrayToolkit::column($mobileProfiles, 'id'),
            ArrayToolkit::column($qqProfiles, 'id')
        );
        $mobileAndQQUsers = $this->getUserService()->findUsersByIds($mobileAndQQUserIds);

        $nicknameUsers = ArrayToolkit::index(
            $this->getUserService()->searchUsers(array('nickname' => $keyword), array('nickname' => 'ASC'), 0, 5),
            'id'
        );

        $users = ArrayToolkit::mergeArraysValue(array($mobileAndQQUsers, $nicknameUsers));
        foreach ($users as $key => $user) {
            if ($user['destroyed'] == 1) {
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
