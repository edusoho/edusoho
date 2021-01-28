<?php

namespace Codeages\PluginBundle\Tests\Event\Fixture;

use Symfony\Component\HttpKernel\Kernel;
use Codeages\PluginBundle\System\PluginableHttpKernelInterface;
use Codeages\PluginBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Codeages\PluginBundle\System\PluginConfigurationManager;

class TestKernel extends Kernel implements PluginableHttpKernelInterface
{
    public function getCacheDir()
    {
        return dirname(__DIR__).'/app/cache';
    }

    public function registerBundles()
    {
        $bundles = array(
            new FrameworkBundle(),
        );

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/app/config/config.yml');
    }

    public function getPluginConfigurationManager()
    {
        return new PluginConfigurationManager(__DIR__);
    }
}
