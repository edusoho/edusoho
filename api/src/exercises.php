<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;
use Topxia\Api\Util\UserUtil;
use Silex\Application;

$api = $app['controllers_factory'];

$api->get('/{id}', function ($id) {
    ServiceKernel::instance()->
    $exercise = convert($id,'exercise');
    return filter($exercise, 'exercise');
});


