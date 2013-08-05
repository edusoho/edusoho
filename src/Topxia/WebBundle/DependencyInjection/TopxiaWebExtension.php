<?php

namespace Topxia\WebBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class TopxiaWebExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
    	$loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    public function getAlias()
    {
        return 'topxia_web';
    }
}
