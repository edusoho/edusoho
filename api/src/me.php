<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

$api = $app['controllers_factory'];

//获取当前用户信息
/*
 ** 响应 **

```
{
"xxx": "xxx"
}
```
 */
$api->get('/', function (Request $request) {
    $user = getCurrentUser();
    $user = is_array($user) ? $user : $user->toArray();
    return filter($user, 'me');
}

);

//获得当前用户的关注者
/*

 ** 响应 **

```
{
"xxx": "xxx"
}
```

 */
$api->get('/followers', function (Request $request) {
    $user = getCurrentUser();
    $follwers = ServiceKernel::instance()->createService('User.UserService')->findAllUserFollower($user['id']);
    return $follwers;
}

);

//获得当前用户关注的人
/*

 ** 响应 **

```
{
"xxx": "xxx"
}
```

 */
$api->get('/followings', function (Request $request) {
    $user = getCurrentUser();
    $follwings = ServiceKernel::instance()->createService('User.UserService')->findAllUserFollowing($user['id']);
    return $follwings;
}

);

//获得当前用户虚拟币账户信息
$api->get('/accounts', function () {
    $user = getCurrentUser();
    $accounts = ServiceKernel::instance()->createService('Cash.CashAccountService')->getAccountByUserId($user['id']);

    if (empty($accounts)) {
        throw new \Exception('accounts not found');
    }

    return $accounts;
}

);

/*
## 获取当前用户的话题
GET /me/coursethreads

[支持分页](global-parameter.md)

 ** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| type | string | 否 | 类型,未传则取全部类型 |

`type`的值有：

 * question : 问答
 * discussion : 话题

 ** 响应 **

```
{
"xxx": "xxx"
}
```
 */

$api->get('/coursethreads', function (Request $request) {
    $user = getCurrentUser();
    $start = $request->query->get('start', 0);
    $limit = $request->query->get('limit', 10);
    $type = $request->query->get('type', '');
    $conditions = empty($type) ? array() : array('type' => $type);
    $conditions['userId'] = $user['id'];
    $total = ServiceKernel::instance()->createService('Course.ThreadService')->searchThreadCount($conditions);
    $coursethreads = ServiceKernel::instance()->createService('Course.ThreadService')->searchThreads($conditions, 'created', $start, $limit);

    return array(
        'data'  => $coursethreads,
        'total' => $total
    );
}

);

/*
## 获取当前用户黑名单
GET /me/blacklists

 ** 响应 **

```
[
{
id:{blacklist-id},
userId:{blacklist-userId},
...
},
{
id:{blacklist-id},
userId:{blacklist-userId},
...
},
...
]
```

 */

$api->get('/blacklists', function () {
    $user = getCurrentUser();
    $blacklists = ServiceKernel::instance()->createService('User.BlacklistService')->findBlacklistsByUserId($user['id']);
    return filters($blacklists, 'blacklist');
}

);

/*
## 获取当前用户互粉用户
GET /me/friends

[支持分页](global-parameter.md)

 ** 响应 **

```
{
"data": "{friend-list}"
"total": "{totalCount}"
}
```

 */

$api->get('/friends', function (Request $request) {
    $user = getCurrentUser();
    $start = $request->query->get('start', 0);
    $limit = $request->query->get('limit', 10);
    $friends = ServiceKernel::instance()->createService('User.UserService')->findFriends($user['id'], $start, $limit);
    $count = ServiceKernel::instance()->createService('User.UserService')->findFriendCount($user['id']);
    return array(
        'data'  => filters($friends, 'user'),
        'total' => $count
    );
}

);

/*
## 获取当前用户通知
GET /me/notifications

[支持分页](global-parameter.md)

 ** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| type | string | 否 | 类型,未传则取全部类型 |

`type`的值有：

 * user-follow : 关注好友

 ** 响应 **

```
{
"data": "{friend-list}"
"total": "{totalCount}"
}
```

 */

$api->get('/notifications', function (Request $request) {
    $user = getCurrentUser();
    $start = $request->query->get('start', 0);
    $limit = $request->query->get('limit', 10);
    $type = $request->query->get('type', '');
    $conditions['userId'] = $user['id'];

    if (!empty($type)) {
        $conditions['type'] = $type;
    }

    $notifications = ServiceKernel::instance()->createService('User.NotificationService')->searchNotifications(
        $conditions,
        array('createdTime', 'DESC'),
        $start,
        $limit
    );
    $count = ServiceKernel::instance()->createService('User.NotificationService')->searchNotificationCount($conditions);
    return array(
        'data'  => filters($notifications, 'notification'),
        'total' => $count
    );
}

);

return $api;
