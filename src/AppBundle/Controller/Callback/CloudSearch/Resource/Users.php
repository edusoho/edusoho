<?php

namespace AppBundle\Controller\Callback\CloudSearch\Resource;

use AppBundle\Controller\Callback\CloudSearch\BaseProvider;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;

class Users extends BaseProvider
{
    public function get(Request $request)
    {
        $conditions = $request->query->all();

        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 100);

        $conditions['updatedTime_GE'] = $conditions['cursor'];
        $users = $this->getUserService()->searchUsers($conditions, array('updatedTime' => 'ASC'), $start, $limit);
        $next = $this->nextCursorPaging($conditions['cursor'], $start, $limit, $users);

        return $this->wrap($this->filter($users), $next);
    }

    public function filter($res)
    {
        return $this->multicallFilter('user', $res);
    }

    protected function multicallFilter($name, array $res)
    {
        $ids = ArrayToolkit::column($res, 'id');
        $profiles = $this->getUserService()->findUserProfilesByIds($ids);

        foreach ($res as $key => $one) {
            $res[$key]['profile'] = $profiles[$one['id']];
            $res[$key] = $this->callFilter($name, $one);
        }

        return $res;
    }

    /**
     * @return Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }
}
