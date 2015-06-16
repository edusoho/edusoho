<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Silex\Application;

$api = $app['controllers_factory'];

//根据id获取一个用户信息

$api->get('/{id}', function ($id) {
    $user = convert($id,'user');
    return filter($user, 'user');
});

//用户模糊查询(qq,手机,昵称)
/*
** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| q | string | 是 | 用于匹配的字段值,分别模糊匹配手机,qq,昵称,每种匹配返回一个列表,每个列表最多五个 |

** 响应 **

```
{
    "mobile": [
        datalist
    ],
    "qq": [
        datalist
    ],
    "nickname": [
        datalist
    ]
}
```
*/
$api->get('/', function (Request $request) {
    $field = $request->query->get('q');
    $mobileProfiles = ServiceKernel::instance()->createService('User.UserService')->searchUserProfiles(array('mobile' => $field), array('id','DESC'), 0, 5);
    $qqProfiles = ServiceKernel::instance()->createService('User.UserService')->searchUserProfiles(array('qq' => $field), array('id','DESC'), 0, 5);
    
    $mobileList = ServiceKernel::instance()->createService('User.UserService')->findUsersByIds(ArrayToolkit::column($mobileProfiles,'id'));
    $qqList = ServiceKernel::instance()->createService('User.UserService')->findUsersByIds(ArrayToolkit::column($qqProfiles,'id'));
    $nicknameList = ServiceKernel::instance()->createService('User.UserService')->searchUsers(array('nickname' => $field), array('createdTime','DESC'), 0, 5);
    return array(
        'mobile' => $mobileList,
        'qq' => $qqList,
        'nickname' => $nicknameList
    );
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
    "success": bool
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
| userId | int | 否 | 发起关注操作的用户id,未传则默认为当前用户 |
| method | string | 否 | 值为delete,表明当前为delete方法 |

** 响应 **

```
{
    "success": bool
}
```
*/
$api->post('/{id}/followers', function (Request $request, $id) {
    $userId = $request->request->get('userId','');
    $method = $request->request->get('method');
    $fromUser = empty($userId) ? getCurrentUser() : convert($userId,'user');
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
| toId | int | 否 | 被选方的的用户id,未传则默认为当前登录用户 |

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
    $toUser = empty($toId) ? getCurrentUser() : convert($toId,'user');
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


//获得用户虚拟币账户信息
$api->get('{id}/accounts', function ($id) {
    $user = convert($id,'user');
    $accounts = ServiceKernel::instance()->createService('Cash.CashAccountService')->getAccountByUserId($user['id']);
    return $accounts;
});
return $api;