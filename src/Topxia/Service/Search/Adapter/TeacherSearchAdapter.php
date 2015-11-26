<?php
namespace Topxia\Service\Search\Adapter;

use Topxia\Common\ArrayToolkit;

class TeacherSearchAdapter extends AbstractSearchAdapter
{
    public function adapt(array $teachers)
    {
        $adaptResult  = array();
        $myFollowings = array();
        $currentUser  = $this->getCurrentUser();

        $userIds      = ArrayToolkit::column($teachers, 'userId');
        $userProfiles = ArrayToolkit::index($this->getUserService()->findUserProfilesByIds($userIds), 'id');
        $users        = ArrayToolkit::index($this->getUserService()->findUsersByIds($userIds), 'id');

        if (!empty($currentUser['id'])) {
            $myFollowings = $this->getUserService()->filterFollowingIds($currentUser['id'], $userIds);
        }

        foreach ($teachers as $index => $teacher) {
            $teacher['id']          = $teacher['userId'];
            $teacher['profile']     = $userProfiles[$teacher['userId']];
            $teacher['largeAvatar'] = $users[$teacher['userId']]['largeAvatar'];
            $teacher['isFollowed']  = in_array($teacher['userId'], $myFollowings);
            array_push($adaptResult, $teacher);
        }

        return $adaptResult;
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }
}
