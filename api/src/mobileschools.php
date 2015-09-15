<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Silex\Application;
use Topxia\Service\Util\EdusohoTuiClient;

$api = $app['controllers_factory'];

/*
## 获取手机网校简介
    GET /mobileschools/about

** 响应 **

```
{
    'about' => 'xxx'
}
```
*/

$api->get('/about', function () {
    $mobile = ServiceKernel::instance()->createService('System.SettingService')->get('mobile', array());
    return array(
        'about' => isset($mobile['about']) ? $mobile['about'] : ''
    );
});

/*
## 获取手机网校token
    GET /mobileschools/token

** 响应 **

```
{
    'token' => 'vPb16d4L9YFm9mqlvTyoCo0Y5og1vZL'
}
```
*/

$api->get('/token', function () {
    $tuiClient = new EdusohoTuiClient();
    $token = $tuiClient->getToken();
    if (isset($token['error'])) {
        throw new Exception($token['error']);
    }
    return $token;
});

/*
## 获取手机网校应用
    GET /mobileschools/apps

** 响应 **

```
{
    'id' => {app-id},
    'name' => {app-name},
    'title' => {app-title},
    'about' => {app-about},
    'avatar' => {app-avatar},
    'callback' => {app-callback}
}
```
*/

$api->get('/apps', function () {
    $mobile = ServiceKernel::instance()->createService('System.SettingService')->get('mobile');
    $site = ServiceKernel::instance()->createService('System.SettingService')->get('site');
    $apps[] = array(
        'id' => 1,
        'code' => 'announcement',
        'name' => $site['name'],
        'title' => $site['slogan'],
        'about' => $mobile['about'],
        'avatar' => $mobile['logo'],
        'callback' => '/mobileschools/announcements'
    );

    $apps[] = array(
        'id' => 2,
        'code' => 'news',
        'name' => '资讯',
        'title' => '',
        'about' => '',
        'avatar' => '',
        'callback' => '',
    );

    return $apps;
});

/*
## 获取手机网校公告列表
    GET /mobileschools/announcements

[支持分页](global-parameter.md)

** 响应 **

```
{
    'data': '{data-list}',
    'total': {data-total}
}
```
*/

$api->get('/announcements', function (Request $request) {
    $start = $request->query->get('start', 0);
    $limit = $request->query->get('limit', 10);
    $count = ServiceKernel::instance()->createService('Announcement.AnnouncementService')->searchAnnouncementsCount(array('targetType' => 'global'));
    $announcements = ServiceKernel::instance()->createService('Announcement.AnnouncementService')->searchAnnouncements(
        array('targetType' => 'global'),
        array('createdTime','DESC'),
        $start,
        $limit
    );
    
    return array(
        'data' => filters($announcements,'announcement'),
        'total' => $count
    );
});

return $api;