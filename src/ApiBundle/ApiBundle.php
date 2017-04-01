<?php

namespace ApiBundle;

use ApiBundle\Api\PathParser;
use ApiBundle\Api\Resource\ResourceManager;
use ApiBundle\Api\ResourceKernel;
use ApiBundle\Api\Util\ObjectCombinationUtil;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApiBundle extends Bundle
{
    const API_PREFIX = '/api';

    public function boot()
    {
        parent::boot();
        $container = $this->container;
        $biz = $container->get('biz');

        $biz['api.router'] = $container->get('router');
        $biz['api.templating'] = $container->get('templating');

        $biz['api.resource.manager'] = function ($biz) {
            return new ResourceManager($biz);
        };

        $biz['api.path.parser'] = function () {
            return new PathParser();
        };

        $biz['api.resource.kernel'] = function($biz) {
            return new ResourceKernel($biz['api.path.parser'], $biz['api.resource.manager']);
        };

        $biz['api.util.oc'] = function($biz) {
            return new ObjectCombinationUtil($biz);
        };
    }
}
