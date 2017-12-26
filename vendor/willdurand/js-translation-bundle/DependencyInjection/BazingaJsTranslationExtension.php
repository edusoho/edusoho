<?php

namespace Bazinga\Bundle\JsTranslationBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

/**
 * @author William DURAND <william.durand1@gmail.com>
 * @author Hugo Monteiro <hugo.monteiro@gmail.com>
 */
class BazingaJsTranslationExtension extends Extension
{
    /**
     * Load configuration.
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor     = new Processor();
        $configuration = new Configuration($container->getParameter('kernel.debug'));
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('controllers.xml');

        $container
            ->getDefinition('bazinga.jstranslation.controller')
            ->replaceArgument(5, $config['locale_fallback'])
            ->replaceArgument(6, $config['default_domain'])
            ->replaceArgument(7, $config['http_cache_time']);

        // Add fallback locale to active locales if missing
        if ($config['active_locales'] && !in_array($config['locale_fallback'], $config['active_locales'])) {
            array_push($config['active_locales'], $config['locale_fallback']);
        }

        $container
            ->getDefinition('bazinga.jstranslation.translation_dumper')
            ->replaceArgument(3, $config['locale_fallback'])
            ->replaceArgument(4, $config['default_domain'])
            ->replaceArgument(5, $config['active_locales'])
            ->replaceArgument(6, $config['active_domains']);
    }
}
