<?php

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ExtensionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('extension.manager')) {
            return;
        }

        $managerDefinition = $container->findDefinition('extension.manager');
        $collectorDefinition = $container->findDefinition('biz.service_provider.collector');

        $taggedServices = $container->findTaggedServiceIds('extension');

        foreach ($taggedServices as $id => $tags) {
            $def = $container->getDefinition($id);

            if (is_subclass_of($def->getClass(), 'Pimple\ServiceProviderInterface')) {
                $collectorDefinition->addMethodCall('add', array(new Reference($id)));
            }

            $managerDefinition->addMethodCall('addExtension', array(new Reference($id)));
        }
    }
}
