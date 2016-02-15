<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Bundle\AsseticBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Finishes configuration of the Sprockets filter.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class SprocketsFilterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('assetic.filter.sprockets')) {
            return;
        }

        $filter = $container->getDefinition('assetic.filter.sprockets');
        foreach ($container->getParameter('assetic.filter.sprockets.include_dirs') as $dir) {
            $filter->addMethodCall('addIncludeDir', array($dir));
        }
    }
}
