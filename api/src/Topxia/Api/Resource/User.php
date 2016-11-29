<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class User extends BaseResource
{
    private $_unsetFields = array(
        'password', 'salt', 'payPassword', 'payPasswordSalt'
    );

    private $_publicFields = array(
        'id', 'nickname', 'title', 'point', 'smallAvatar', 'mediumAvatar', 'largeAvatar', 'createdTime', 'updatedTime'
    );

    private $_publicProfileFields = array(
        'about'
    );

    private $_privateFields = array(
        'id', 'nickname', 'title', 'tags', 'type', 'roles',
        'point', 'coin', 'smallAvatar', 'mediumAvatar', 'largeAvatar',
        'email', 'emailVerified', 'promoted', 'promotedTime', 'locked', 'lockDeadline',
        'loginTime', 'loginIp', 'approvalTime', 'approvalStatus', 'newMessageNum', 'newNotificationNum',
        'createdIp', 'createdTime', 'updatedTime'
    );

    private $_privateProfileFields = array(
        'truename', 'idcard', 'gender', 'birthday', 'city', 'mobile', 'qq',
        'signature', 'about', 'company', 'job', 'school', 'class', 'weibo', 'weixin', 'site'
    );

    public function get(Application $app, Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        if (empty($user)) {
            return $this->error(404, "用户(#{$id})不存在");
        }

        $user['profile'] = $this->getUserService()->getUserProfile($id);

        return $this->filter($user);
    }

    public function post(Application $app, Request $request, $id)
    {
        $data    = $request->request->all();

        $type = $data['type'];

        if (empty($type)) {
            return $this->errer('5005', '没有type字段');
        }

        $method = 'call_'.ucfirst($type);

        if (method_exists($this, $method)) {
            return call_user_func(array($this, $method), $request);
        } else {
            return $this->error('5006', 'type字段无效');
        }
    }

    public function filter($res)
    {
        foreach ($this->_unsetFields as $key) {
            unset($res[$key]);
        }

        $currentUser = getCurrentUser();

        $returnRes = array();

        if ($currentUser->isAdmin() || ($currentUser['id'] == $res['id'])) {
            foreach ($this->_privateFields as $key) {
                $returnRes[$key] = $res[$key];
            }

            if (!empty($res['profile'])) {
                foreach ($this->_privateProfileFields as $key) {
                    $returnRes[$key] = $res['profile'][$key];
                }
            }
        } else {
            foreach ($this->_publicFields as $key) {
                $returnRes[$key] = $res[$key];
            }

            // if (in_array('ROLE_TEACHER', $returnRes['roles'])) {
            //     $returnRes['roles'] = array('ROLE_TEACHER');
            // } else {
            //     $returnRes['roles'] = array('ROLE_USER');
            // }
            if (!empty($res['profile'])) {
                foreach ($this->_publicProfileFields as $key) {
                    $returnRes[$key] = $res['profile'][$key];
                }
            }
        }

        $res = $returnRes;

        foreach (array('smallAvatar', 'mediumAvatar', 'largeAvatar') as $key) {
            $res[$key] = $this->getFileUrl($res[$key]);
        }

        foreach (array('promotedTime', 'loginTime', 'approvalTime', 'createdTime') as $key) {
            if (!isset($res[$key])) {
                continue;
            }

            $res[$key] = date('c', $res[$key]);
        }

        $res['updatedTime'] = date('c', $res['updatedTime']);

        return $res;
    }

    public function simplify($res)
    {
        $simple = array();

        $simple['id']       = $res['id'];
        $simple['nickname'] = $res['nickname'];
        $simple['title']    = $res['title'];
        $simple['avatar']   = $this->getFileUrl($res['smallAvatar']);

        return $simple;
    }

    protected function call_bind_mobile($request)
    {
        $user    = $this->getCurrentUser();
        $data    = $request->request->all();
        $mobile  = empty($data['mobile']) ? null : $data['mobile'];
        $smsCode = empty($data['sms_code']) ? null : $data['sms_code'];

        //手机验证码的校验
        $result = true;

        if (!$result) {
            return $this->error('5007', '手机验证码错误');
        }

        $this->getUserService()->changeMobile($user['id'], $mobile);

        return array('code' => 0);
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
