<?php

namespace Topxia\Api\Resource\IM;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class MeLogin extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $user = $this->getCurrentUser();
        $message = array(
            'clientId'   => $user['id'],
            'tag' => $request->request->get('tag', ''),
            'deviceKernel' => $request->request->get('deviceKernel', ''),
            'deviceVersion' => $request->request->get('deviceVersion', ''),
            'deviceToken' => $request->request->get('deviceToken', ''),
            'desiceName' => $request->request->get('desiceName', ''),
            'ignoreServers' => $request->request->get('ignoreServers', ''),
        );
        //@todo leaf
        return CloudAPIFactory::create('root')->get('/im/login', $message);
    }

    public function filter($res)
    {
        return $res;
    }
}
