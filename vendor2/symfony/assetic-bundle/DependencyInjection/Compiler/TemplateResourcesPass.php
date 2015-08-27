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

use Symfony\Bundle\AsseticBundle\DependencyInjection\DirectoryResourceDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This pass adds directory resources to scan for assetic assets.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class TemplateResourcesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('assetic.asset_manager')) {
            return;
        }

        $engines = $container->getParameter('templating.engines');

        // bundle and kernel resources
        $bundles = $container->getParameter('kernel.bundles');
        $asseticBundles = $container->getParameterBag()->resolveValue($container->getParameter('assetic.bundles'));
        foreach ($asseticBundles as $bundleName) {
            $rc = new \ReflectionClass($bundles[$bundleName]);
            foreach ($engines as $engine) {
                $this->setBundleDirectoryResources($container, $engine, dirname($rc->getFileName()), $bundleName);
            }
        }

        foreach ($engines as $engine) {
            $this->setAppDirectoryResources($container, $engine);
        }
    }

    protected function setBundleDirectoryResources(ContainerBuilder $container, $engine, $bundleDirName, $bundleName)
    {
        $container->setDefinition(
            'assetic.'.$engine.'_directory_resource.'.$bundleName,
            new DirectoryResourceDefinition($bundleName, $engine, array(
                $container->getParameter('kernel.root_dir').'/Resources/'.$bundleName.'/views',
                $bundleDirName.'/Resources/views',
            ))
        );
    }

    protected function setAppDirectoryResources(ContainerBuilder $container, $engine)
    {
        $container->setDefinition(
            'assetic.'.$engine.'_directory_resource.kernel',
            new DirectoryResourceDefinition('', $engine, array($container->getParameter('kernel.root_dir').'/Resources/views'))
        );
    }
}
