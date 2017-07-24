<?php

namespace Codeages\PluginBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EventSubscriberPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $subscriberServices = $container->findTaggedServiceIds('codeages_plugin.event.subscriber');

        if (empty($subscriberServices)) {
            return;
        }

        $lazySubscribers = $container->getDefinition('codeags_plugin.event.lazy_subscribers');

        foreach ($subscriberServices as $id => $tags) {
            $lazySubscribers->addMethodCall('addSubscriberService', array($id));
        }
    }
}
