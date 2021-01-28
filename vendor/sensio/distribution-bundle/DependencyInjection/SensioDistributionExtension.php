<?php

namespace Sensio\Bundle\DistributionBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

/**
 * SensioDistributionExtension.
 *
 * @author Marc Weistroff <marc.weistroff@sensio.com>
 */
class SensioDistributionExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('security.xml');
    }

    public function getNamespace()
    {
        return 'http://symfony.com/schema/dic/symfony/sensiodistribution';
    }

    public function getAlias()
    {
        return 'sensio_distribution';
    }
}
