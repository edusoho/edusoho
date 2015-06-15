<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

$api = $app['controllers_factory'];

//根据id获取一个公告信息
$api->get('/{id}', function ($id) {
    $announcement = convert($id,'announcement');
    return filter($announcement, 'announcement');
});


//公告信息
/*
[支持分页](global-parameter.md)

** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| targetId | int | 是 | 对象id |
| targetType | string | 是 | 对象类型 |

`targetType`的值有:
  
  * course : 课程
  * classroom : 班级
  * global : 全站

** 响应 **

```
{
    "xxx": "xxx"
}
```

*/
$api->get('/', function (Request $request) {
    $targetId = $request->query->get('targetId');
    $targetType = $request->query->get('targetType');
    $start = $request->query->get('start');
    $limit = $request->query->get('limit');

    $announcements = ServiceKernel::instance()->createService('Announcement.AnnouncementService')->searchAnnouncements(array('targetType'=>$targetType,'targetId'=>$targetId),array('createdTime','DESC'),$start,$limit);
    return $announcements;
});

return $api;