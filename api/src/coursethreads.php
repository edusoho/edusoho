<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

$api = $app['controllers_factory'];

//获取话题信息
/*
** 响应 **

```
{
    "xxx": "xxx"
}
```
*/
$api->get('/{id}', function ($id) {
    $thread = convert($id, 'courseThread');
    return filter($thread, 'courseThread');
});

//新增话题
/*
** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| courseId | int | 是 | 课程id |
| type | string | 否 | 类型,默认为discussion |
| title | string | 是 | 标题 |
| content | string | 是 | 内容 |

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

$api->post('/', function (Request $request) {
    $fields['courseId'] = $request->request->get('courseId', 0);
    $fields['type'] = $request->request->get('type', 'discussion');
    $fields['title'] = $request->request->get('title');
    $fields['content'] = $request->request->get('content');

    $thread = ServiceKernel::instance()->createService('Course:ThreadService')->createThread($fields);
    return filter($thread, 'courseThread');
});

/*
## 修改话题
    POST /coursethreads/{id}

** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| title | string | 否 | 标题 |
| content | string | 否 | 内容 |

** 响应 **

```
{
    "xxx": xxx
}
```
*/
$api->post('/{id}', function (Request $request, $id) {
    $title = $request->request->get('title','');
    $content = $request->request->get('content','');
    $thread = convert($id, 'courseThread');
        
    $conditions['title'] = empty($title) ? $thread['title'] : $title;
    $conditions['content'] = empty($content) ? $thread['content'] : $content;
    $thread = ServiceKernel::instance()->createService('Course:ThreadService')->updateThread(2, $thread['id'], $conditions);
    return filter($thread, 'courseThread');
});

/*
## 获得单个话题回复
    GET /threads/posts/{id}

** 响应 **

```
{
    "xxx": "xxx"
}
```
*/
$api->get('/posts/{id}', function ($id) {
    $post = convert($id, 'courseThreadPost');
    return filter($post, 'courseThreadPost');
});

/*
## 回复话题
    POST /coursethreads/{id}/posts

** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| content | string | 是 | 内容 |

** 响应 **

```
{
    "xxx": "xxx"
}
```
*/
$api->post('/{id}/posts', function (Request $request, $id) {
    $content = $request->request->get('content');
    $thread = convert($id, 'courseThread');
    $post = empty($content) ? array() : array('content' => $content);
    $post['threadId'] = $thread['id'];
    $post['courseId'] = $thread['courseId'];
    $post = ServiceKernel::instance()->createService('Course:ThreadService')->createPost($post);
    return filter($post, 'courseThreadPost');
});

/*
## 获得话题的教师回复(尚有问题)
    GET /threads/{id}/teacher_posts

[支持分页](global-parameter.md)

** 响应 **

```
{
    "xxx": "xxx"
}
```
*/
$api->get('/{id}/teacher_posts', function (Request $request, $id) {
    $thread = convert($id, 'courseThread');
    $start = $request->query->get('start',0);
    $limit = $request->query->get('limit',10);
    $posts = ServiceKernel::instance()->createService('Course:ThreadService')->findThreadElitePosts($thread['courseId'], $thread['id'], $start, $limit);
    return $posts;
});
return $api;