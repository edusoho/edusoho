<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\DistributionBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to add steps to the web configurator.
 *
 * @author Jérôme Vieilledent <lolautruche@gmail.com>
 */
class StepsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sensio_distribution.webconfigurator')) {
            return;
        }

        $configuratorDef = $container->findDefinition('sensio_distribution.webconfigurator');
        foreach ($container->findTaggedServiceIds('webconfigurator.step') as $id => $tags) {
            $priority = isset($tags[0]['priority']) ? $tags[0]['priority'] : 0;
            $configuratorDef->addMethodCall('addStep', array(new Reference($id), $priority));
        }
    }
}
