<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

$api = $app['controllers_factory'];

//获取当前用户信息
/*
** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| token | string | 是 | 当前登录用户token |

** 响应 **

```
{
    "xxx": "xxx"
}
```

*/
$api->get('/', function (Request $request) {
    $user = convert($request->query->get('token'),'me');
    return filter($user, 'me');
});

//获取当前用户课程
/*
[支持分页](global-parameter.md)

** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| token | string | 是 | 当前登录用户token |
| type | string | 否 | 课程类型 |
| relation | string | 否 | 用户相对于课程状态 |


`type`的值有：

  * normal : 普通课程
  * live : 直播课程

`relation`的值有：

  * learning : 在学
  * learned : 已学完
  * teaching : 在教
  * favorited : 收藏

** 响应 **

```
{
    "xxx": "xxx"
}
```
*/
$api->get('/courses', function (Request $request) {
    $conditions = $request->query->all();
    $user = convert($conditions['token'],'me');
    if ($conditions['relation'] == 'learning') {
        $total = ServiceKernel::instance()->createService('Course.CourseService')->findUserLeaningCourseCount($user['id'],$conditions);
        $courses = ServiceKernel::instance()->createService('Course.CourseService')->findUserLeaningCourses(
            $user['id'], 
            $conditions['start'], 
            $conditions['limit'], 
            array('type' => $conditions['type'])
        );
    } elseif ($conditions['relation'] == 'learned') {
        $total = ServiceKernel::instance()->createService('Course.CourseService')->findUserLeanedCourseCount($user['id'],$conditions);
        $courses = ServiceKernel::instance()->createService('Course.CourseService')->findUserLeanedCourses(
            $user['id'], 
            $conditions['start'], 
            $conditions['limit'], 
            array('type' => $conditions['type'])
        );
    } elseif ($conditions['relation'] == 'teaching') {
        $total = ServiceKernel::instance()->createService('Course.CourseService')->findUserTeachCourseCount(array('userId' => $user['id']), false);
        $courses = ServiceKernel::instance()->createService('Course.CourseService')->findUserTeachCourses(
            array('userId' => $user['id']),
            $conditions['start'], 
            $conditions['limit'], 
            false
        );
    } else if ($conditions['relation'] == 'favorited') {
        $total = ServiceKernel::instance()->createService('Course.CourseService')->findUserFavoritedCourseCount($user['id']);
        $courses = ServiceKernel::instance()->createService('Course.CourseService')->findUserFavoritedCourses(
            $user['id'],
            $conditions['start'], 
            $conditions['limit']
        );
    } else {
        //全部
        // $courses = array();
        // $total = 0;
    }
    return array(
        'data' => $courses,
        'total' => $total
    );
});


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
    $user = convert($request->query->get('token'),'me');
    $follwers = ServiceKernel::instance()->createService('User.UserService')->findAllUserFollower($user['id']);
    return $follwers;
});

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
    $user = convert($request->query->get('token'),'me');
    $follwings = ServiceKernel::instance()->createService('User.UserService')->findAllUserFollowing($user['id']);
    return $follwings;
});

return $api;