<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Silex\Application;

$api = $app['controllers_factory'];

/*
## 获取手机网校简介
    POST /mobileschools/about

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
return $api;