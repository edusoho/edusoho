<?php

namespace Bazinga\Bundle\JsTranslationBundle\Tests;

// get the autoload file
$dir = __DIR__;
$lastDir = null;
while ($dir !== $lastDir) {
    $lastDir = $dir;

    if (is_file($dir.'/autoload.php')) {
        require_once $dir.'/autoload.php';
        break;
    }

    if (is_file($dir.'/autoload.php.dist')) {
        require_once $dir.'/autoload.php.dist';
        break;
    }

    $dir = dirname($dir);
}

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * App Test Kernel for functional tests.
 */
class AppKernel extends Kernel
{
    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);
    }

    public function registerBundles()
    {
        return array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle(),
            new \Bazinga\Bundle\JsTranslationBundle\Tests\Fixtures\app\TestingPurposesBundle\TestingPurposesBundle()
        );
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/'.Kernel::VERSION.'/bazinga-js-translation/cache/'.$this->environment;
    }

    public function getLogDir()
    {
        return sys_get_temp_dir().'/'.Kernel::VERSION.'/bazinga-js-translation/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        if (self::VERSION_ID < 20800) {
            $loader->load(__DIR__.'/config/older_versions_config.yml');
        } else {
            $loader->load(__DIR__.'/config/'.$this->environment.'.yml');
        }

        if (self::VERSION_ID > 30200) {
            $loader->load(__DIR__.'/config/disable_annotations.yml');
        }
    }

    public function serialize()
    {
        return serialize(array($this->getEnvironment(), $this->isDebug()));
    }

    public function unserialize($str)
    {
        call_user_func_array(array($this, '__construct'), unserialize($str));
    }
}
