<?php

namespace ApiBundle\Api\Resource\Crontab;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CrontabStatus extends AbstractResource
{
    public function get(ApiRequest $request)
    {
        if ($this->getCurrentUser()->isAdmin()) {
            throw new AccessDeniedHttpException();
        }

        
    }
}