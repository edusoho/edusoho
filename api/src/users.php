<?php

use AppBundle\Common\Exception\ResourceNotFoundException;
use AppBundle\Common\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

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

    return [
        'user' => filter($user, 'user'),
        'token' => $token,
    ];
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

    return [
        'success' => $result ? $result : false,
    ];
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

    if (!empty($method) && 'delete' == $method) {
        $result = ServiceKernel::instance()->createService('User:UserService')->unFollow($fromUser['id'], $id);
    } else {
        $result = ServiceKernel::instance()->createService('User:UserService')->follow($fromUser['id'], $id);
    }

    return [
        'success' => empty($result) ? false : true,
    ];
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
        $toIds = [$currentUser['id']];
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
