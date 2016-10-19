<?php

namespace Codeages\PluginBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class CodeagesPluginExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $bundles = $container->getParameter('kernel.bundles');

        $files = array();

        foreach ($bundles as $bundleClass) {
            $refClass = new \ReflectionClass($bundleClass);
            $file = dirname($refClass->getFileName()) . '/Resources/config/dict.yml';

            if (file_exists($file) === true) {
                $files[] = $file;
            }
        }

        $collector = $container->getDefinition('codeages_plugin.dict_collector');
        $collector->replaceArgument(0, $files);
    }
}
