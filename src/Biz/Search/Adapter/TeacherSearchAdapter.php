<?php

namespace Biz\Search\Adapter;

use AppBundle\Common\ArrayToolkit;

class TeacherSearchAdapter extends AbstractSearchAdapter
{
    public function adapt(array $teachers)
    {
        $adaptResult = array();
        $myFollowings = array();
        $user = $this->getCurrentUser();

        $userIds = ArrayToolkit::column($teachers, 'userId');
        $userProfiles = ArrayToolkit::index($this->getUserService()->findUserProfilesByIds($userIds), 'id');
        $users = ArrayToolkit::index($this->getUserService()->findUsersByIds($userIds), 'id');

        if (!empty($user['id'])) {
            $myFollowings = $this->getUserService()->filterFollowingIds($user['id'], $userIds);
        }

        foreach ($teachers as $index => $teacher) {
            if (array_key_exists($teacher['userId'], $users)) {
                $teacher['profile'] = array_key_exists($teacher['userId'], $userProfiles) ? $userProfiles[$teacher['userId']] : array();
                $teacher['largeAvatar'] = $users[$teacher['userId']]['largeAvatar'];
                $teacher['isFollowed'] = in_array($teacher['userId'], $myFollowings);
            } else {
                $teacher['profile'] = array();
                $teacher['largeAvatar'] = '';
            }
            $teacher['id'] = $teacher['userId'];
            array_push($adaptResult, $teacher);
        }

        return $adaptResult;
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
