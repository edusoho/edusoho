<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\SwiftmailerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * RegisterPluginsPass registers Swiftmailer plugins.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class RegisterPluginsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->findDefinition('swiftmailer.mailer') || !$container->getParameter('swiftmailer.mailers')) {
            return;
        }

        $mailers = $container->getParameter('swiftmailer.mailers');
        foreach ($mailers as $name => $mailer) {
            $plugins = $container->findTaggedServiceIds(sprintf('swiftmailer.%s.plugin', $name));
            $transport = sprintf('swiftmailer.mailer.%s.transport', $name);
            $definition = $container->findDefinition($transport);
            foreach ($plugins as $id => $args) {
                $definition->addMethodCall('registerPlugin', array(new Reference($id)));
            }
        }
    }
}
