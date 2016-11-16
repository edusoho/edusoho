<?php


namespace Codeages\PluginBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;


class EventSubscriberPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if(!$container->has('codeages_plugin.event.subscribers')){
            return;
        }

        $subscribers = $container->findDefinition('codeages_plugin.event.subscribers');

        $subscriberServices = $container->findTaggedServiceIds('codeages_plugin.event.subscriber');

        foreach ($subscriberServices as $id => $tags){
            $subscribers->addMethodCall('addSubscriber', array($container->getDefinition($id)->getClass()));
        }
    }
}