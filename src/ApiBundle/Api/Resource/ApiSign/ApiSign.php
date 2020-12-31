<?php

namespace ApiBundle\Api\Resource\ApiSign;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class ApiSign extends AbstractResource
{
    /**
     * @param ApiRequest $request
     * @return string
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request)
    {
        return 'test123456';
    }
}