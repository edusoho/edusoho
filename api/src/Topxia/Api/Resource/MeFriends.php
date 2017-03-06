<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class MeFriends extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $user = getCurrentUser();
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);
        $friends = $this->getUserService()->findFriends($user['id'], $start, $limit);
        $count = $this->getUserService()->findFriendCount($user['id']);
        return array(
            'data'  => filters($friends, 'user'),
            'total' => $count
        );
    }

    public function filter($res)
    {

    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }
}
