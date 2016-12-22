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
use Symfony\Component\Yaml\Yaml;

/**
 * This pass adds Assetic routes when use_controller is true.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class RouterResourcePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $useController  = $container->getParameterBag()->resolveValue($container->getParameter('assetic.use_controller'));
        $routerResource = $container->getParameterBag()->resolveValue($container->getParameter('router.resource'));

        if (!$useController || !$routerResource) {
            return;
        }

        $file = $container->getParameter('kernel.cache_dir').'/assetic/routing.yml';

        if (!is_dir($dir = dirname($file))) {
            mkdir($dir, 0777, true);
        }

        $contents = array(
            '_assetic' => array('resource' => '.', 'type' => 'assetic'),
            '_app'     => array('resource' => $container->getParameter('router.resource')),
        );

        $router = $container->findDefinition('router.default');
        $routerConfigs = $router->getArgument(2);

        if (isset($routerConfigs['resource_type'])) {
            $contents['_app']['type'] = $routerConfigs['resource_type'];
        }

        $routerConfigs['resource_type'] = 'yaml';

        $router->replaceArgument(2, $routerConfigs);
        $container->setParameter('router.resource', $file);

        file_put_contents($file, Yaml::dump($contents));
    }
}
