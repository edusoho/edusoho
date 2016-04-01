<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class MeIMLogin extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $user = $this->getCurrentUser();
        $message = array(
            'clientId'   => $user['id'],
            'clientName' => $user['nickname']
        );
        return CloudAPIFactory::create('leaf')->get('/im/login', $message);
    }

    public function filter($res)
    {
        return $res;
    }
}
