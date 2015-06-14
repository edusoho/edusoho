<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

$api = $app['controllers_factory'];

//根据id获取一个用户信息
$api->get('/{id}', function ($id) {
    $user = convert($id,'user');
    return filter($user, 'user');
});

//注册
$api->post('/', function (Request $request) {
    $fields = $request->request->all();
    $user = ServiceKernel::instance()->createService('User.UserService')->register($fields);
    return filter($user, 'user');
});


//登陆
$api->post('/login', function (Request $request) {
    $fields = $request->request->all();
    $user = ServiceKernel::instance()->createService('User.UserService')->getUserByLoginField($fields['nickname']);
    if (empty($user)) {
        throw new \Exception('user not found');
    }
    if (!ServiceKernel::instance()->createService('User.UserService')->verifyPassword($user['id'], $fields['password'])) {
        throw new \Exception('password error');
    }

    $token = ServiceKernel::instance()->createService('User.UserService')->makeToken('login',$user['id']);
    $user = filter($user, 'user');
    return array(
        'user' => $user,
        'token' => $token
    );
});

//登出
$api->post('/logout', function (Request $request) {
    $token = $request->request->get('token');
    $result = ServiceKernel::instance()->createService('User.UserService')->deleteToken('login',$token);
    return array(
        'success' => $result ? $result :false
    );
});

return $api;