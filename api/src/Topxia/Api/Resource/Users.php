<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class Users extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $conditions = $request->query->all();

        // @deprecated 兼容老接口，即将去除
        if (!empty($conditions['q'])) {
            return $this->matchUsers($conditions['q']);
        }

        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        if (isset($conditions['cursor'])) {
            $conditions['updatedTime_GE'] = (int)$conditions['cursor'];
            $users = $this->getUserService()->searchUsers($conditions, array('updatedTime', 'ASC'), $start, $limit);
            $next = $this->nextCursorPaging($conditions['cursor'], $start, $limit, $users);
            return $this->wrap($this->filter($users), $next);
        } else {
            $users = $this->getUserService()->searchUsers($conditions, array('createdTime','DESC'), $start, $limit);
            $total = $this->getUserService()->searchUserCount($conditions);
            return $this->wrap($this->filter($users), $total);
        }

    }

    public function post(Application $app, Request $request)
    {
        $fields = $request->request->all();
        $user = $this->getUserService()->register($fields);
        $user['profile'] = $this->getUserService()->getUserProfile($user['id']);
        return $this->callFilter('User', $user);
    }

    public function filter(&$res)
    {
        return $this->multicallFilter('User', $res);
    }

    protected function multicallFilter($name, &$res)
    {
        $ids = ArrayToolkit::column($res, 'id');
        $profiles = $this->getUserService()->findUserProfilesByIds($ids);

        foreach ($res as &$user) {
            $user['profile'] = $profiles[$user['id']];
            $this->callFilter($name, $user);
        }
        return $res;
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    /**
     * 用户模糊查询
     */
    private function matchUsers($q)
    {
        $mobileProfiles = $this->getUserService()->searchUserProfiles(array('mobile' => $q), array('id', 'DESC'), 0, 5);
        $qqProfiles = $this->getUserService()->searchUserProfiles(array('qq' => $q), array('id', 'DESC'), 0, 5);

        $mobileList = $this->getUserService()->findUsersByIds(ArrayToolkit::column($mobileProfiles, 'id'));
        $qqList = $this->getUserService()->findUsersByIds(ArrayToolkit::column($qqProfiles, 'id'));
        $nicknameList = $this->getUserService()->searchUsers(array('nickname' => $q), array('LENGTH(nickname)', 'ASC'), 0, 5);

        return array(
            'mobile' => filters($mobileList, 'user'),
            'qq' => filters($qqList, 'user'),
            'nickname' => filters($nicknameList, 'user'),
        );
    }
}
