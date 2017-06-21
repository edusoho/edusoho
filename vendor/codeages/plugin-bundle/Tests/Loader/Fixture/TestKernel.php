<?php

namespace Codeages\PluginBundle\Tests\Loader\Fixture;

use Codeages\PluginBundle\System\PluginableHttpKernelInterface;
use Codeages\PluginBundle\System\PluginConfigurationManager;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel implements PluginableHttpKernelInterface
{
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
