<?php

namespace Codeages\PluginBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Finder\Finder;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
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

        $this->loadDicts($bundles, $container);
        $this->loadSlots($bundles, $container);
    }

    public function loadDicts($bundles, $container)
    {
        $files = array();
        foreach ($bundles as $bundleClass) {
            $refClass = new \ReflectionClass($bundleClass);
            $fileDir = dirname($refClass->getFileName()).'/Resources/config';
            if (file_exists($fileDir) === false) {
                continue;
            }
            $finder = Finder::create()
                ->files()
                ->name('dict.*.yml')
                ->in($fileDir);
            foreach ($finder as $file) {
                $file = $file->getRealPath();
                if (file_exists($file) === true) {
                    $files[] = $file;
                }
            }
        }
        $collector = $container->getDefinition('codeages_plugin.dict_collector');
        $collector->replaceArgument(0, $files);
    }

    public function loadSlots($bundles, $container)
    {
        $files = array();

        foreach ($bundles as $bundleClass) {
            $refClass = new \ReflectionClass($bundleClass);
            $file = dirname($refClass->getFileName()).'/Resources/config/slots.yml';

            if (file_exists($file) === true) {
                $files[] = $file;
            }
        }

        $collector = $container->getDefinition('codeages_plugin.slot_collector');
        $collector->replaceArgument(0, $files);
    }
}
