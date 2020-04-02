<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Bundle\AsseticBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Semantic asset configuration.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class AsseticExtension extends Extension
{
    /**
     * Loads the configuration.
     *
     * @param array            $configs   An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('assetic.xml');
        $loader->load('templating_twig.xml');
        $loader->load('templating_php.xml');

        $def = $container->getDefinition('assetic.parameter_bag');
        if (method_exists($def, 'setFactory')) {
            // to be inlined in assetic.xml when dependency on Symfony DependencyInjection is bumped to 2.6
            $def->setFactory(array(new Reference('service_container'), 'getParameterBag'));
        } else {
            // to be removed when dependency on Symfony DependencyInjection is bumped to 2.6
            $def->setFactoryService('service_container');
            $def->setFactoryMethod('getParameterBag');
        }

        $processor = new Processor();
        $configuration = $this->getConfiguration($configs, $container);
        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter('assetic.debug', $config['debug']);
        $container->setParameter('assetic.use_controller', $config['use_controller']['enabled']);
        $container->setParameter('assetic.enable_profiler', $config['use_controller']['profiler']);
        $container->setParameter('assetic.read_from', $config['read_from']);
        $container->setParameter('assetic.write_to', $config['write_to']);
        $container->setParameter('assetic.variables', $config['variables']);

        $container->setParameter('assetic.java.bin', $config['java']);
        $container->setParameter('assetic.node.bin', $config['node']);
        $container->setParameter('assetic.node.paths', $config['node_paths']);
        $container->setParameter('assetic.ruby.bin', $config['ruby']);
        $container->setParameter('assetic.sass.bin', $config['sass']);
        $container->setParameter('assetic.reactjsx.bin', $config['reactjsx']);

        // register formulae
        $formulae = array();
        foreach ($config['assets'] as $name => $formula) {
            $formulae[$name] = array($formula['inputs'], $formula['filters'], $formula['options']);
        }

        if ($formulae) {
            $container->getDefinition('assetic.config_resource')->replaceArgument(0, $formulae);
        } else {
            $container->removeDefinition('assetic.config_loader');
            $container->removeDefinition('assetic.config_resource');
        }

        // register filters
        foreach ($config['filters'] as $name => $filter) {
            if (isset($filter['resource'])) {
                $loader->load($container->getParameterBag()->resolveValue($filter['resource']));
                unset($filter['resource']);
            } else {
                $loader->load('filters/'.$name.'.xml');
            }

            if (isset($filter['file'])) {
                $container->getDefinition('assetic.filter.'.$name)->setFile($filter['file']);
                unset($filter['file']);
            }

            if (isset($filter['apply_to'])) {
                if (!is_array($filter['apply_to'])) {
                    $filter['apply_to'] = array($filter['apply_to']);
                }

                foreach ($filter['apply_to'] as $i => $pattern) {
                    $worker = new DefinitionDecorator('assetic.worker.ensure_filter');
                    $worker->replaceArgument(0, '/'.$pattern.'/');
                    $worker->replaceArgument(1, new Reference('assetic.filter.'.$name));
                    $worker->addTag('assetic.factory_worker');

                    $container->setDefinition('assetic.filter.'.$name.'.worker'.$i, $worker);
                }

                unset($filter['apply_to']);
            }

            foreach ($filter as $key => $value) {
                $container->setParameter('assetic.filter.'.$name.'.'.$key, $value);
            }
        }

        // twig functions
        $container->setParameter('assetic.twig_extension.functions', $config['twig']['functions']);

        // choose dynamic or static
        if ($useController = $container->getParameterBag()->resolveValue($container->getParameterBag()->get('assetic.use_controller'))) {
            $loader->load('controller.xml');
            $container->getDefinition('assetic.helper.dynamic')->addTag('templating.helper', array('alias' => 'assetic'));
            $container->removeDefinition('assetic.helper.static');
        } else {
            $container->getDefinition('assetic.helper.static')->addTag('templating.helper', array('alias' => 'assetic'));
            $container->removeDefinition('assetic.helper.dynamic');
        }

        $container->setParameter('assetic.bundles', $config['bundles']);

        if ($config['workers']['cache_busting']['enabled']) {
            $container->getDefinition('assetic.worker.cache_busting')->addTag('assetic.factory_worker');
        }
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__ . '/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://symfony.com/schema/dic/assetic';
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        return new Configuration(array_keys($bundles));
    }
}
