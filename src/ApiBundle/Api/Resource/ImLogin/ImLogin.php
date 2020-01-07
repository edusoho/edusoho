<?php

namespace ApiBundle\Api\Resource\ImLogin;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\CloudPlatform\IMAPIFactory;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ImLogin extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $user = $this->getCurrentUser();

        $message = array(
            'clientId' => $user['id'],
            'tag' => $request->request->get('tag', ''),
            'deviceKernel' => $request->request->get('deviceKernel', ''),
            'deviceVersion' => $request->request->get('deviceVersion', ''),
            'deviceToken' => $request->request->get('deviceToken', ''),
            'deviceName' => $request->request->get('deviceName', ''),
            'ignoreServers' => $request->request->get('ignoreServers', ''),
        );

        $server = IMAPIFactory::create()->get('/login', $message);

        if (isset($server['error'])) {
            throw new BadRequestHttpException($server['error']['message']);
        }

        return $server;
    }
}
