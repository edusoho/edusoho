<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

$api = $app['controllers_factory'];

//根据id获取一个用户信息

$api->get('/{id}', function ($id) {
    $user = convert($id,'user');
    return filter($user, 'user');
});

//用户模糊查询(qq,手机,昵称)
$api->get('/', function (Request $request) {
    $field = $request->query->get('field');

});

//注册
/*
** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| email | string | 是 | 邮箱 |
| nickname | string | 是 | 昵称 |
| password | string | 是 | 密码 |

** 响应 **

```
{
    "xxx": "xxx"
}
```
*/
$api->post('/', function (Request $request) {
    $fields = $request->request->all();
    $user = ServiceKernel::instance()->createService('User.UserService')->register($fields);
    return filter($user, 'user');
});


//登陆
/*
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
    $user = ServiceKernel::instance()->createService('User.UserService')->getUserByLoginField($fields['nickname']);
    if (empty($user)) {
        throw new \Exception('user not found');
    }
    if (!ServiceKernel::instance()->createService('User.UserService')->verifyPassword($user['id'], $fields['password'])) {
        throw new \Exception('password error');
    }

    $token = ServiceKernel::instance()->createService('User.UserService')->makeToken('login',$user['id']);
    $user = filter($user, 'user');
    return array(
        'user' => $user,
        'token' => $token
    );
});

//登出
$api->post('/logout', function (Request $request) {
    $token = $request->request->get('token');
    $result = ServiceKernel::instance()->createService('User.UserService')->deleteToken('login',$token);
    return array(
        'success' => $result ? $result :false
    );
});

//开通会员
/*
** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| levelId | int | 是 | 会员等级id |
| boughtUnit | string | 是 | 开通时长 |
| boughtDuration | string | 是 | 付费方式 |

`boughtDuration`的值有:

  * month : 按月
  * year : 按年

** 响应 **

```
{
    "xxx": "xxx"
}
```
*/
$api->post('/{id}/vips', function (Request $request, $id) {
    $user = convert($id,'user');
    $levelId = $request->request->get('levelId');
    $boughtDuration= $request->request->get('boughtDuration');
    $boughtUnit= $request->request->get('boughtUnit');

    $member = ServiceKernel::instance()->createService('Vip:Vip.VipService')->becomeMember(
        $user['id'], 
        $levelId, 
        $boughtDuration, 
        $boughtUnit,
        $orderId = 0
    );

    return array(
        'success' => empty($member) ? false : true
    );
});

// 关注用户,method为delete时为取消关注用户
/*
** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| userId | int | 否 | 发起关注操作的用户id |
| token | string | 否 | 当前登陆用户token |
| method | string | 否 | 值为delete,表明当前为delete方法 |

`userId`和`token`两者必需且只能传一个

** 响应 **

```
{
    "xxx": "xxx"
}
```
*/
$api->post('/{id}/followers', function (Request $request, $id) {
    $userId = $request->request->get('userId');
    $token = $request->request->get('token');
    $method = $request->request->get('method');
    $fromUser = empty($userId) ? convert($token,'me') : convert($userId,'user');
    if (!empty($method) && $method == 'delete') {
        $result = ServiceKernel::instance()->createService('User.UserService')->unFollow($fromUser['id'],$id);
    } else {
        $result = ServiceKernel::instance()->createService('User.UserService')->follow($fromUser['id'],$id);
    }
    return array(
        'success' => empty($result) ? false : true
    );
});


//获得用户的关注者
/*

** 响应 **

```
{
    "xxx": "xxx"
}
```

*/
$api->get('/{id}/followers', function ($id) {
    $user = convert($id,'user');
    $follwers = ServiceKernel::instance()->createService('User.UserService')->findAllUserFollower($user['id']);
    return $follwers;
});

//获得用户关注的人
/*

** 响应 **

```
{
    "xxx": "xxx"
}
```

*/
$api->get('/{id}/followings', function ($id) {
    $user = convert($id,'user');
    $follwings = ServiceKernel::instance()->createService('User.UserService')->findAllUserFollowing($user['id']);
    return $follwings;
});

//获得用户的好友关系
/*

** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| toId | int | 否 | 被选方的的用户id |
| token | string | 否 | 当前登陆用户token |

`toId`和`token`两者必需且只能传一个

** 响应 **

```
{
    "friendship": "none"
}
```
friendship的值有：

none : 双方无关系
following : id用户关注了toId用户
follower : toId用户关注了id用户
friend : 互相关注
*/
$api->get('/{id}/friendship', function (Request $request, $id) {
    $user = convert($id,'user');
    $toId = $request->query->get('toId');
    $token = $request->query->get('token');
    $toUser = empty($toId) ? convert($token,'me') : convert($toId,'user');
    //关注id的人
    $follwers = ServiceKernel::instance()->createService('User.UserService')->findAllUserFollower($user['id']);
    //id关注的人
    $follwings = ServiceKernel::instance()->createService('User.UserService')->findAllUserFollowing($user['id']);
    
    $toId = $toUser['id'];
    if (!empty($follwers[$toId])) {
        return !empty($follwings[$toId]) ? array('friendship' => 'friend') : array('friendship' => 'follower');
    }

    return !empty($follwings[$toId]) ? array('friendship' => 'following') : array('friendship' => 'none');
});

return $api;