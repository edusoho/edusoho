<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class Users extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $conditions = $request->query->all();

        if (empty($conditions)) {
            return array();
        }

        // @deprecated 兼容老接口，即将去除
        if (!empty($conditions['q'])) {
            return $this->matchUsers($conditions['q']);
        }

        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 100);

        if (isset($conditions['cursor'])) {
            $conditions['updatedTime_GE'] = $conditions['cursor'];
            $users = $this->getUserService()->searchUsers($conditions, array('updatedTime' => 'ASC'), $start, $limit);
            $next = $this->nextCursorPaging($conditions['cursor'], $start, $limit, $users);
            return $this->wrap($this->filter($users), $next);
        } else {
            $users = $this->getUserService()->searchUsers($conditions, array('createdTime' => 'DESC'), $start, $limit);
            $total = $this->getUserService()->countUsers($conditions);
            return $this->wrap($this->filter($users), $total);
        }
    }

    public function post(Application $app, Request $request)
    {
        $fields = $request->request->all();

        if (!ArrayToolkit::requireds($fields, array('email', 'nickname', 'password'))) {
            return array('message' => '缺少必填字段');
        }

        if (empty($fields['registeredWay']) || !in_array(strtolower($fields['registeredWay']), array('ios', 'android'))) {
            $fields['registeredWay'] = $this->guessDeviceFromUserAgent($request->headers->get("user-agent"));
        }

        $ip = $request->getClientIp();
        $fields['createdIp'] = $ip;

        $authSettings = ServiceKernel::instance()->createService('System:SettingService')->get('auth', array());

        if (isset($authSettings['register_protective'])) {
            $type = $authSettings['register_protective'];

            switch ($type) {
                case 'middle':
                    $condition = array(
                        'startTime' => time() - 24 * 3600,
                        'createdIp' => $ip);
                    $registerCount = $this->getUserService()->countUsers($condition);

                    if ($registerCount > 30) {
                        goto failure;
                    }

                    goto register;
                    break;
                case 'high':
                    $condition = array(
                        'startTime' => time() - 24 * 3600,
                        'createdIp' => $ip);
                    $registerCount = $this->getUserService()->countUsers($condition);

                    if ($registerCount > 10) {
                        goto failure;
                    }

                    $registerCount = $this->getUserService()->countUsers(array(
                        'startTime' => time() - 3600,
                        'createdIp' => $ip));

                    if ($registerCount >= 1) {
                        goto failure;
                    }

                    goto register;
                    break;
                default:
                    goto register;
                    break;
            }
        }

        register:
        $user = $this->getUserService()->register($fields, array('mobile'));
        $user['profile'] = $this->getUserService()->getUserProfile($user['id']);
        return $this->callFilter('User', $user);

        failure:
        return array('message' => '已经超出用户注册次数限制，用户注册失败');
    }

    public function filter($res)
    {
        return $this->multicallFilter('User', $res);
    }

    protected function multicallFilter($name, $res)
    {
        $ids = ArrayToolkit::column($res, 'id');
        $profiles = $this->getUserService()->findUserProfilesByIds($ids);

        foreach ($res as $key => $one) {
            $res[$key]['profile'] = $profiles[$one['id']];
            $res[$key] = $this->callFilter($name, $one);
        }
        return $res;
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    /**
     * 用户模糊查询
     */
    private function matchUsers($q)
    {
        $mobileProfiles = $this->getUserService()->searchUserProfiles(array('mobile' => $q), array('id' => 'DESC'), 0, 5);
        $qqProfiles = $this->getUserService()->searchUserProfiles(array('qq' => $q), array('id' => 'DESC'), 0, 5);

        $mobileList = $this->getUserService()->findUsersByIds(ArrayToolkit::column($mobileProfiles, 'id'));
        $qqList = $this->getUserService()->findUsersByIds(ArrayToolkit::column($qqProfiles, 'id'));
        $nicknameList = $this->getUserService()->searchUsers(array('nickname' => $q), array('nickname' => 'ASC'), 0, 5);

        return array(
            'mobile' => filters($mobileList, 'user'),
            'qq' => filters($qqList, 'user'),
            'nickname' => filters($nicknameList, 'user'),
        );
    }
}
