<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

$api = $app['controllers_factory'];

/*
## 根据id获取一个黑名单信息
    GET /blacklists/{id}

** 响应 **

```
{
    "xxx": "xxx"
}
```

*/
$api->get('/{id}', function ($id) {
    $blacklist = convert($id,'blacklist');
    return filter($blacklist, 'blacklist');
});

/*
## 添加（删除）黑名单
    POST /blacklists

** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| blackId | int | 是 | 被加入黑名单的用户id |
| method | string | 否 | delete时表示删除，其他均为新增 |

** 响应 **

```
{
    "xxx": "xxx"
}
```

*/
$api->post('/', function (Request $request) {
    $blackId = $request->request->get('blackId',0);
    $userId = $request->request->get('userId',0);
    $user = getCurrentUser();
    $method = $request->request->get('method','post');
    if ($method == 'delete') {
        $result = ServiceKernel::instance()->createService('User:BlacklistService')->deleteBlacklistByUserIdAndBlackId($user['id'],$blackId);
        return array(
            'success' => $result>0 ? true : false
        );
    }
    $blacklist['userId'] = $user['id'];
    $blacklist['blackId'] = $blackId;
    $blacklist = ServiceKernel::instance()->createService('User:BlacklistService')->addBlacklist($blacklist);
    return filter($blacklist, 'blacklist');
});

return $api;