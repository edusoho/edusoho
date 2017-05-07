<?php

namespace ApiBundle;

use ApiBundle\Api\PathParser;
use ApiBundle\Api\Resource\FieldFilterFactory;
use ApiBundle\Api\Resource\ResourceManager;
use ApiBundle\Api\ResourceKernel;
use ApiBundle\Api\Util\AssetHelper;
use ApiBundle\Api\Util\ObjectCombinationUtil;
use ApiBundle\Security\Authentication\DefaultResourceAuthenticationProvider;
use ApiBundle\Security\Authentication\ResourceAuthenticationProviderManager;
use ApiBundle\Security\Authentication\Token\AnonymousToken;
use ApiBundle\Security\Firewall\AnonymousListener;
use ApiBundle\Security\Firewall\Firewall;
use ApiBundle\Security\Firewall\TokenHeaderListener;
use Codeages\PluginBundle\System\PluginConfigurationManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

        $this->initEnv();

        $container->get('api_firewall')->addListener($container->get('api_basic_authentication_listener'));
        $container->get('api_firewall')->addListener($container->get('api_token_header_listener'));
        $container->get('api_firewall')->addListener($container->get('api_anonymous_listener'));
        $container->get('api_authentication_manager')->addProvider($container->get('api_default_authentication'));
    }

    private function initEnv()
    {
        AssetHelper::setContainer($this->container);
    }
}
