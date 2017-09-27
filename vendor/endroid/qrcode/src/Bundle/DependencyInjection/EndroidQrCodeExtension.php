<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Endroid\QrCode\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
class EndroidQrCodeExtension extends \Symfony\Component\HttpKernel\DependencyInjection\Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, \Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $processor = new \Symfony\Component\Config\Definition\Processor();
        $config = $processor->processConfiguration(new \Endroid\QrCode\Bundle\DependencyInjection\Configuration(), $configs);
        $loader = new \Symfony\Component\DependencyInjection\Loader\YamlFileLoader($container, new \Symfony\Component\Config\FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $factoryDefinition = $container->getDefinition('endroid.qrcode.factory');
        $factoryDefinition->setArguments(array($config));
    }
}