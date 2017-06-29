<?php

namespace Topxia\Api\Resource\IM;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;
use Biz\CloudPlatform\IMAPIFactory;

class MeLogin extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $user    = $this->getCurrentUser();
        $message = array(
            'clientId'      => $user['id'],
            'tag'           => $request->request->get('tag', ''),
            'deviceKernel'  => $request->request->get('deviceKernel', ''),
            'deviceVersion' => $request->request->get('deviceVersion', ''),
            'deviceToken'   => $request->request->get('deviceToken', ''),
            'deviceName'    => $request->request->get('deviceName', ''),
            'ignoreServers' => $request->request->get('ignoreServers', '')
        );

        return IMAPIFactory::create()->get('/login', $message);
    }

    public function filter($res)
    {
        return $res;
    }
}
