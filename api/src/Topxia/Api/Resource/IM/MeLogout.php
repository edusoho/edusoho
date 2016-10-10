<?php

namespace Topxia\Api\Resource\IM;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\IMAPIFactory;

class MeLogout extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $user = $this->getCurrentUser();
        $clientId = $user['id'];
        if ($clientId <= 0) {
            return $this->error(500, "clientid no empty");
        }
        $message = array(
            'mute' => 1
        );

        return IMAPIFactory::create()->post('/me/clients/' . $clientId, $message);
    }

    public function filter($res)
    {
        return $res;
    }
}
