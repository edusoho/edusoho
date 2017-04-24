<?php

namespace Symfony\Bundle\AsseticBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class StaticAsseticHelperPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('assetic.helper.static')) {
            return;
        }

        if ($container->hasDefinition('assets.packages')) {
            return;
        }

        // Templating disabled in Symfony <2.6 or on 2.7+, remove the assetic helper
        if (!$container->hasDefinition('templating.helper.assets') || '' === $container->getDefinition('templating.helper.assets')->getArgument(0)) {
            $container->removeDefinition('assetic.helper.static');

            return;
        }

        $definition = $container->getDefinition('assetic.helper.static');
        $definition->replaceArgument(0, new Reference('templating.helper.assets'));

        if (!method_exists($definition, 'setShared') && 'request' === $container->getDefinition('templating.helper.assets')->getScope()) {
            $definition->setScope('request');
        }
    }
}
