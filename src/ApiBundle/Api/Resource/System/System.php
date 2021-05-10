<?php

namespace ApiBundle\Api\Resource\System;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;

class System extends AbstractResource
{
    private $supportTypes = [
        'timestamp',
    ];

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $type)
    {
        $this->checkType($type);
        $method = "get${type}";

        return $this->$method($request);
    }

    private function checkType($type)
    {
        if (!in_array($type, $this->supportTypes)) {
            throw CommonException::ERROR_PARAMETER();
        }
    }

    public function getTimestamp()
    {
        return time();
    }
}
