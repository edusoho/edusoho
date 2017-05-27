<?php

namespace ApiBundle;

use ApiBundle\Api\Util\AssetHelper;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApiBundle extends Bundle
{
    const API_PREFIX = '/api';

    public function boot()
    {
        parent::boot();
        $this->initEnv();
    }

    private function initEnv()
    {
        AssetHelper::setContainer($this->container);
    }
}
