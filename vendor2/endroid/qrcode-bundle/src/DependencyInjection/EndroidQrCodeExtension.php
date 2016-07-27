<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\Bundle\QrCodeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class EndroidQrCodeExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $config    = $processor->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        if (isset($config['size'])) {
            $container->setParameter('endroid_qrcode.size', $config['size']);
        }

        if (isset($config['padding'])) {
            $container->setParameter('endroid_qrcode.padding', $config['padding']);
        }

        if (isset($config['extension'])) {
            $container->setParameter('endroid_qrcode.extension', $config['extension']);
        }

        if (isset($config['error_correction_level'])) {
            $container->setParameter('endroid_qrcode.error_correction_level', $config['error_correction_level']);
        }

        if (isset($config['foreground_color'])) {
            $container->setParameter('endroid_qrcode.foreground_color', $config['foreground_color']);
        }

        if (isset($config['background_color'])) {
            $container->setParameter('endroid_qrcode.background_color', $config['background_color']);
        }

        if (isset($config['label'])) {
            $container->setParameter('endroid_qrcode.label', $config['label']);
        }

        if (isset($config['label_font_size'])) {
            $container->setParameter('endroid_qrcode.label_font_size', $config['label_font_size']);
        }

        if (isset($config['label_font_path'])) {
            $container->setParameter('endroid_qrcode.label_font_path', $config['label_font_path']);
        }
    }
}
