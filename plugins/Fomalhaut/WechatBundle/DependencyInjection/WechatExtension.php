<?php

namespace Fomalhaut\WechatBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\DependencyInjection\Definition;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class WechatExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $property_access = PropertyAccess::createPropertyAccessor();

        $debug = $property_access->getValue($config, '[debug]');
        $clients = $property_access->getValue($config, '[clients]');

        $definitions = array();

        foreach ($clients as $name => $parameters) {
            // 要添加的ServiceId
            $service_id = sprintf('fomalhaut_wechat.%s.sdk.wechat', $name);

            // 判断这个ServiceId是否已经出现，防止覆盖
            if (!$container->hasDefinition($service_id)) {
                $token = $property_access->getValue($parameters, '[token]');
                $appid = $property_access->getValue($parameters, '[appid]');
                $appsecret = $property_access->getValue($parameters, '[appsecret]');

                // 初始化Service定义类
                $definition = new Definition();
                $definition->setClass('Fomalhaut\\WechatBundle\\WechatSDK\\Wechat');
                $definition->addMethodCall('setContainer', array(new Reference('service_container')));
                $definition->setArguments(array($name, $token, $appid, $appsecret, $debug));

                $definitions[$service_id] = $definition;
            } else
                throw new \RuntimeException(sprintf('Service %s is exist', $service_id));
        }
        $container->addDefinitions($definitions);
    }
}
