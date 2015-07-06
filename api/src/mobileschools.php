<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Silex\Application;

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
    $token = ServiceKernel::instance()->createService('EduCloud.EduCloudService')->getToken();
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
        'name' => $site['name'],
        'title' => $site['slogan'],
        'about' => $mobile['about'],
        'avatar' => $mobile['logo'],
        'callback' => '/mobileschools/announcements'
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

/*
## 获取web端网校第三方登陆设置

    GET /mobileschools/bind_info

** 响应 **

```
{
    'qq': '{qq-data}',
    'weibo': {weibo-data}
}
```
*/

$api->get('/bind_info', function (Request $request) {
    $bindInfo = ServiceKernel::instance()->createService('System.SettingService')->get('login_bind');
    $weiboInfo= array(
        'enabled' => $bindInfo['weibo_enabled'],
        'key' => $bindInfo['weibo_key'],
        'secret' => $bindInfo['weibo_secret'],
        'set_fill_account' => $bindInfo['weibo_set_fill_account']
    );

    $qqInfo= array(
        'enabled' => $bindInfo['qq_enabled'],
        'key' => $bindInfo['qq_key'],
        'secret' => $bindInfo['qq_secret'],
        'set_fill_account' => $bindInfo['qq_set_fill_account']
    );
    return array(
        'weibo' => $weiboInfo,
        'qq' => $qqInfo
    );
});

return $api;