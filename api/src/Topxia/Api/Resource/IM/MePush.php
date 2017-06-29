<?php

namespace Topxia\Api\Resource\IM;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;
use Biz\CloudPlatform\IMAPIFactory;

class MePush extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $mute = $request->request->get('mute', 0);

        $user     = $this->getCurrentUser();
        $clientId = $user['id'];
        if ($clientId <= 0) {
            return $this->error(500, "clientid no empty");
        }

        $message = array(
            'mute' => $mute
        );

        return IMAPIFactory::create()->post('/me/clients/'.$clientId, $message);
    }

    public function filter($res)
    {
        return $res;
    }
}
