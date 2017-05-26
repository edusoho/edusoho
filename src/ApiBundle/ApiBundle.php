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
        $container = $this->container;
        $this->initEnv();

        $container->get('api_firewall')->addListener($container->get('api_oauth2_authentication_listener'));
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
