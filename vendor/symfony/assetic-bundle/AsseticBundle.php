<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Bundle\AsseticBundle;

use Symfony\Bundle\AsseticBundle\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Assetic integration.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class AsseticBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new Compiler\TemplateResourcesPass());
        $container->addCompilerPass(new Compiler\CheckClosureFilterPass());
        $container->addCompilerPass(new Compiler\CheckCssEmbedFilterPass());
        $container->addCompilerPass(new Compiler\CheckYuiFilterPass());
        $container->addCompilerPass(new Compiler\SprocketsFilterPass());
        $container->addCompilerPass(new Compiler\TemplatingPass());
        $container->addCompilerPass(new Compiler\AssetFactoryPass());
        $container->addCompilerPass(new Compiler\AssetManagerPass());
        $container->addCompilerPass(new Compiler\FilterManagerPass());
        $container->addCompilerPass(new Compiler\RouterResourcePass());
        $container->addCompilerPass(new Compiler\StaticAsseticHelperPass());
    }
}
