<?php

namespace ApiBundle;

use ApiBundle\Api\PathParser;
use ApiBundle\Api\Resource\ResourceManager;
use ApiBundle\Api\ResourceKernel;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApiBundle extends Bundle
{
    const API_PREFIX = '/api/v1';

    public function boot()
    {
        parent::boot();
        $biz = $this->container->get('biz');
        $biz['api.resource.manager'] = function ($biz) {
            return new ResourceManager($biz);
        };

        $biz['api.path.parser'] = function () {
            return new PathParser();
        };

        $biz['api.resource.kernel'] = function($biz) {
            return new ResourceKernel($biz['api.path.parser'], $biz['api.resource.manager']);
        };
    }
}
