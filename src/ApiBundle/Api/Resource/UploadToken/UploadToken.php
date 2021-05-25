<?php

namespace ApiBundle\Api\Resource\UploadToken;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class UploadToken extends AbstractResource
{
    public function get(ApiRequest $request, $group)
    {
        $uploadToken = new \AppBundle\Util\UploadToken();
        $token = $uploadToken->make($group, 'image', 18000);

        return [
            'token' => $token,
            'expiry' => date('c', time() + 18000),
        ];
    }
}
