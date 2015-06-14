<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

$api = $app['controllers_factory'];

//根据id获取一个课程信息
$api->get('/{id}', function ($id) {
    $course = convert($id,'course');
    return filter($course, 'course');
});


return $api;