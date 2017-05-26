<?php

namespace ApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class ApiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        // load services
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->loadListeners($container);
    }

    private function loadListeners(ContainerBuilder $container)
    {
        $container->getDefinition('api_firewall')
            ->replaceArgument(0, array(
                new Reference('api_oauth2_authentication_listener'),
                new Reference('api_basic_authentication_listener'),
                new Reference('api_token_header_listener'),
                new Reference('api_anonymous_listener'),
            ));

        $container->getDefinition('api_authentication_manager')
            ->replaceArgument(0, array(
               new Reference('api_default_authentication')
            ));
    }

}
