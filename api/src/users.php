<?php

use Topxia\Service\Common\ServiceKernel;
use Silex\Application;

$api = $app['controllers_factory'];

$api->get('/{id}', function ($id) use ($app) {

	return filter($id, 'user');
});


$api->post('/{id}', function ($id) use ($app) {

	return filter($id, 'user');
});

$api->convert('id', function($id) {
	$user = ServiceKernel::instance()->createService('User.UserService')->getUser($id);

	if (empty($user)) {
		throw new \Exception('Not Found');
	}
	return $user;

});

return $api;