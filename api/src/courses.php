<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

$api = $app['controllers_factory'];

//根据id获取一个课程信息
//$api->get('/{id}', function ($id) {
//    $course = convert($id,'course');
//    return filter($course, 'course');
//});

//收藏课程
/*
** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| method | string | 否 | 值为delete,表明当前为delete方法 |

** 响应 **

```
{
    "xxx": "xxx"
}
```
*/
$api->post('/{id}/favroite', function (Request $request, $id) {
    $course = convert($id,'course');
    $method = $request->request->get('method','post');
    if ($method == 'delete') {
        $result = ServiceKernel::instance()->createService('Course:CourseService')->unFavoriteCourse($course['id']);
    } else {
        $result = ServiceKernel::instance()->createService('Course:CourseService')->favoriteCourse($course['id']);
    }
    return array(
        'success' => $result
    );
});
return $api;