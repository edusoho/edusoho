<?php

namespace ApiBundle\Api\Resource\SecuritySign;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class SecuritySign extends AbstractResource
{
    /**
     * @return string[]
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        return [
            'key' => 'test123456',
        ];
    }
}
