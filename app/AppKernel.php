<?php

use Topxia\Common\ExtensionManager;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    protected $plugins = array();

    protected $extensionManger;

    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);
        date_default_timezone_set('Asia/Shanghai');
        $this->extensionManger = ExtensionManager::init($this);
    }

    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Endroid\Bundle\QrCodeBundle\EndroidQrCodeBundle(),
            new Topxia\WebBundle\TopxiaWebBundle(),
            new Topxia\AdminBundle\TopxiaAdminBundle(),
            new Topxia\MobileBundle\TopxiaMobileBundle(),
            new Topxia\MobileBundleV2\TopxiaMobileBundleV2(),
            new Classroom\ClassroomBundle\ClassroomBundle(),
            new MaterialLib\MaterialLibBundle\MaterialLibBundle(),
            new SensitiveWord\SensitiveWordBundle\SensitiveWordBundle(),
            new Org\OrgBundle\OrgBundle()
        );

        $pluginMetaFilepath = $this->getRootDir().'/data/plugin_installed.php';
        $pluginRootDir      = $this->getRootDir().'/../plugins';

        if (file_exists($pluginMetaFilepath)) {
            $pluginMeta    = include_once $pluginMetaFilepath;
            $this->plugins = $pluginMeta['installed'];

            if (is_array($pluginMeta)) {
                foreach ($pluginMeta['installed'] as $c) {
                    if ($pluginMeta['protocol'] == '1.0') {
                        $c         = ucfirst($c);
                        $p         = base64_decode('QnVuZGxl');
                        $cl        = "{$c}\\".substr(str_repeat("{$c}{$p}\\", 2), 0, -1);
                        $bundles[] = new $cl();
                    } elseif ($pluginMeta['protocol'] == '2.0') {
                        if ($c['type'] != 'plugin') {
                            continue;
                        }

                        $c         = ucfirst($c['code']);
                        $p         = base64_decode('QnVuZGxl');
                        $cl        = "{$c}\\".substr(str_repeat("{$c}{$p}\\", 2), 0, -1);
                        $bundles[] = new $cl();
                    }
                }
            }
        }

        $bundles[] = new Custom\WebBundle\CustomWebBundle();
        $bundles[] = new Custom\AdminBundle\CustomAdminBundle();

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    public function getPlugins()
    {
        return $this->plugins;
    }
}
