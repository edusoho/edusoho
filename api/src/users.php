<?php

use Codeages\Biz\Framework\Event\Event;
use Topxia\Api\Util\UserUtil;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Exception\RuntimeException;
use AppBundle\Common\Exception\ResourceNotFoundException;
use AppBundle\Common\Exception\InvalidArgumentException;

$api = $app['controllers_factory'];

/*

## 登录

POST /users/login

 ** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| nickname | string | 是 | 昵称 |
| password | string | 是 | 密码 |

 ** 响应 **

```
{
"xxx": "xxx"
}
```
 */
$api->post('/login', function (Request $request) {
    $fields = $request->request->all();
    $user = ServiceKernel::instance()->createService('User:UserService')->getUserByLoginField($fields['nickname']);

    if (empty($user)) {
        throw new ResourceNotFoundException('User', $fields['nickname']);
    }

    if (!ServiceKernel::instance()->createService('User:UserService')->verifyPassword($user['id'], $fields['password'])) {

        throw new RuntimeException('password error');
    }

    $token = ServiceKernel::instance()->createService('User:UserService')->makeToken('mobile_login', $user['id']);
    setCurrentUser($user);
    return array(
        'user'  => filter($user, 'user'),
        'token' => $token
    );
}

);

/*

## 第三方登录

POST /users/bind_login

 ** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| type | string | 是 | 第三方类型,值有qq,weibo,weixin,renren |
| id | string | 是 | 第三方处的用户id |
| name | string | 是 | 第三方处的用户昵称 |
| avatar | string | 是 | 第三方处的用户头像 |

 ** 响应 **

```
{
"user": "{user-data}"
"token": "{user-token}"
}
```

此处`token`为ES端记录通过接口登录的用户的唯一凭证

 */
$api->post('/bind_login', function (Request $request) {
    $type = $request->request->get('type');
    $id = $request->request->get('id');
    $name = $request->request->get('name');
    $avatar = $request->request->get('avatar', '');

    if (empty($type)) {
        throw new InvalidArgumentException('type parameter error');
    }

    $userBind = ServiceKernel::instance()->createService('User:UserService')->getUserBindByTypeAndFromId($type, $id);

    if (empty($userBind)) {
        $oauthUser = array(
            'id'        => $id,
            'name'      => $name,
            'avatar'    => $avatar,
            'createdIp' => $request->getClientIp()
        );
        $token = array('userId' => $id);

        if (empty($oauthUser['id'])) {
            throw new InvalidArgumentException("获取用户信息失败，请重试。");
        }

        if (!ServiceKernel::instance()->createService('User:AuthService')->isRegisterEnabled()) {
            throw new RuntimeException("注册功能未开启，请联系管理员！");
        }

        $userUtil = new UserUtil();
        $user = $userUtil->generateUser($type, $token, $oauthUser, $setData = array());

        if (empty($user)) {
            throw new RuntimeException("登录失败，请重试！");
        }

        ServiceKernel::instance()->createService('User:TokenService')->deleteTokenByTypeAndUserId('mobile_login', $user['id']);
        $token = ServiceKernel::instance()->createService('User:UserService')->makeToken('mobile_login', $user['id'], time() + 3600 * 24 * 30);
        setCurrentUser($user);
        $user = $userUtil->fillUserAttr($user['id'], $oauthUser);
    } else {
        $user = ServiceKernel::instance()->createService('User:UserService')->getUser($userBind['toId']);
        ServiceKernel::instance()->createService('User:TokenService')->deleteTokenByTypeAndUserId('mobile_login', $user['id']);
        $token = ServiceKernel::instance()->createService('User:UserService')->makeToken('mobile_login', $user['id'], time() + 3600 * 24 * 30);
        setCurrentUser($user);
    }

    $currentUser = ServiceKernel::instance()->getCurrentUser();
    $currentUser['currentIp'] = $request->getClientIp();
    $biz = ServiceKernel::instance()->getBiz();
    $biz['dispatcher']->dispatch('user.login', new Event($currentUser));

    return array(
        'user'  => filter($user, 'user'),
        'token' => $token
    );
}

);

/*
## 登出

POST /users/logout

 ** 响应 **

```
{
"success": bool
}
```
 */
$api->post('/logout', function (Request $request) {
    $token = $request->request->get('token');
    $result = ServiceKernel::instance()->createService('User:UserService')->deleteToken('login', $token);
    return array(
        'success' => $result ? $result : false
    );
}

);

/*
## （取消）关注用户
POST /users/{id}/followers

 ** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| method | string | 否 | 值为delete时为取消关注用户 |

 ** 响应 **

```
{
"success": bool
}
```
 */
$api->post('/{id}/followers', function (Request $request, $id) {
    $method = $request->request->get('method');
    $fromUser = getCurrentUser();

    if (!empty($method) && $method == 'delete') {
        $result = ServiceKernel::instance()->createService('User:UserService')->unFollow($fromUser['id'], $id);
    } else {
        $result = ServiceKernel::instance()->createService('User:UserService')->follow($fromUser['id'], $id);
    }

    return array(
        'success' => empty($result) ? false : true
    );
}

);

//获得用户的好友关系
/*
## 获得用户的好友关系
GET /users/{id}/friendship

ddddd
| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| toIds | int | 否 | 被选方的的用户id,未传则默认为当前登录用户,多id格式为id-1,id-2,id-3|

 ** 响应 **

```
[
no-user,
none,
following,
follower,
friend,
...
]
```
返回数组，排序与传入id对应,好友关系的值有：

no-user : toId用户不存在
none : 双方无关系
following : id用户关注了toId用户
follower : toId用户关注了id用户
friend : 互相关注
 */
$api->get('/{id}/friendship', function (Request $request, $id) {
    $currentUser = getCurrentUser();
    $user = convert($id, 'user');
    $toIds = $request->query->get('toIds');

    if (!empty($toIds)) {
        $toIds = explode(',', $toIds);
    } else {
        $toIds = array($currentUser['id']);
    }

    foreach ($toIds as $toId) {
        $toUser = ServiceKernel::instance()->createService('User:UserService')->getUser($toId);

        if (empty($toUser)) {
            $result[] = 'no-user';
            continue;
        }

        //关注id的人
        $follwers = ServiceKernel::instance()->createService('User:UserService')->findAllUserFollower($user['id']);
        //id关注的人
        $follwings = ServiceKernel::instance()->createService('User:UserService')->findAllUserFollowing($user['id']);

        $toId = $toUser['id'];

        if (!empty($follwers[$toId])) {
            $result[] = !empty($follwings[$toId]) ? 'friend' : 'follower';
        } else {
            $result[] = !empty($follwings[$toId]) ? 'following' : 'none';
        }
    }

    return $result;
}

);

return $api;
