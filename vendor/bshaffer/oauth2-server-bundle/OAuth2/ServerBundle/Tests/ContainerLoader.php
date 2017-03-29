<?php

namespace OAuth2\ServerBundle\Tests;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class ContainerLoader
{
    public static function buildTestContainer($configs = array())
    {
        if (!isset($_SERVER['CONTAINER_CONFIG'])) {
            throw new \Exception('Must set CONTAINER_CONFIG in phpunit.xml or environment variable');
        }

        $container = new ContainerBuilder();
        $locator   = new FileLocator(__DIR__ . '/..');
        $loader    = new XmlFileLoader($container, $locator);

        $loader->load($_SERVER['CONTAINER_CONFIG']);

        foreach ($configs as $file) {
            $loader->load($file);
        }

        //  give the container some context
        $container->setParameter('bundle_root_dir', __DIR__.'/..');

        return $container;
    }
}