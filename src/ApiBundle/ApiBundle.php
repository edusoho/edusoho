<?php

namespace ApiBundle;

use ApiBundle\Api\PathParser;
use ApiBundle\Api\Resource\FieldFilterFactory;
use ApiBundle\Api\Resource\ResourceManager;
use ApiBundle\Api\ResourceKernel;
use ApiBundle\Api\Util\ObjectCombinationUtil;
use Codeages\PluginBundle\System\PluginConfigurationManager;
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

        $biz['api.util.oc'] = function($biz) {
            return new ObjectCombinationUtil($biz);
        };

        $biz['api.plugin.config.manager'] = function ($biz) use ($container) {
            return new PluginConfigurationManager($container->getParameter('kernel.root_dir'));
        };

        $biz['api.field.filter.factory'] = function ($biz) use ($container) {
            return new FieldFilterFactory($container->get('annotation_reader'));
        };
    }
}
