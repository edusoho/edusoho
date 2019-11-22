<?php

namespace ApiBundle\Api\Resource\ImClient;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\CloudPlatform\IMAPIFactory;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ImClient extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $mute = $request->request->get('mute', 0);

        $user = $this->getCurrentUser();

        $message = array('mute' => $mute);

        $client = IMAPIFactory::create()->post('/me/clients/'.$user['id'], $message);

        if (isset($client['error'])) {
            throw new BadRequestHttpException($client['error']['message']);
        }

        return $client;
    }
}
