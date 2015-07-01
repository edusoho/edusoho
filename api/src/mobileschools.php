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
return $api;