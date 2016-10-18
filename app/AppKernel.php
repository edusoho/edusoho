<?php

use Topxia\Common\ExtensionManager;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;
use Symfony\Component\HttpFoundation\Request;

class AppKernel extends Kernel
{
    protected $plugins = array();

    /**
     * @var Request
     */
    protected $request;

    protected $extensionManger;

    private $isServiceKernelInit = false;

    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);
        date_default_timezone_set('Asia/Shanghai');
        $this->extensionManger = ExtensionManager::init($this);
    }

    public function boot()
    {
        if (true === $this->booted) {
            return;
        }
        parent::boot();
        $this->bootBiz();
        $this->bootServiceKernel();
    }

    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            // new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Endroid\Bundle\QrCodeBundle\EndroidQrCodeBundle(),
            new Topxia\WebBundle\TopxiaWebBundle(),
            new Topxia\AdminBundle\TopxiaAdminBundle(),
            new Topxia\MobileBundle\TopxiaMobileBundle(),
            new Topxia\MobileBundleV2\TopxiaMobileBundleV2(),
            new Classroom\ClassroomBundle\ClassroomBundle(),
            new MaterialLib\MaterialLibBundle\MaterialLibBundle(),
            new SensitiveWord\SensitiveWordBundle\SensitiveWordBundle(),
            new Org\OrgBundle\OrgBundle(),
            new Permission\PermissionBundle\PermissionBundle(),
            new Activity\ActivityBundle\ActivityBundle(),
            new CourseTask\TaskBundle\TaskBundle(),
            new Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle(),
            // new OAuth2\ServerBundle\OAuth2ServerBundle()
        );

        $pluginMetaFilepath = $this->getRootDir() . '/data/plugin_installed.php';
        $pluginRootDir      = $this->getRootDir() . '/../plugins';

        if (file_exists($pluginMetaFilepath)) {
            $pluginMeta    = include_once $pluginMetaFilepath;
            $this->plugins = $pluginMeta['installed'];

            if (is_array($pluginMeta)) {
                foreach ($pluginMeta['installed'] as $c) {
                    if ($pluginMeta['protocol'] == '1.0') {
                        $c         = ucfirst($c);
                        $p         = base64_decode('QnVuZGxl');
                        $cl        = "{$c}\\" . substr(str_repeat("{$c}{$p}\\", 2), 0, -1);
                        $bundles[] = new $cl();
                    } elseif ($pluginMeta['protocol'] == '2.0') {
                        if ($c['type'] != 'plugin') {
                            continue;
                        }

                        $c         = ucfirst($c['code']);
                        $p         = base64_decode('QnVuZGxl');
                        $cl        = "{$c}\\" . substr(str_repeat("{$c}{$p}\\", 2), 0, -1);
                        $bundles[] = new $cl();
                    }
                }
            }
        }

        $bundles[] = new Custom\WebBundle\CustomWebBundle();
        $bundles[] = new Custom\AdminBundle\CustomAdminBundle();

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            // $bundles[] = new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
    }

    public function getPlugins()
    {
        return $this->plugins;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    protected function bootBiz()
    {
        $biz = $this->getContainer()->get('biz');
        $biz['migration.directories'][] = dirname(__DIR__) . '/migrations';
        $biz->register(new \Codeages\Biz\Framework\Provider\DoctrineServiceProvider());
        $biz->boot();
    }

    protected function bootServiceKernel()
    {
        if(!$this->isServiceKernelInit){
            $container     = $this->getContainer();
            $biz = $container->get('biz');
            $serviceKernel = ServiceKernel::create($this->getEnvironment(), $this->isDebug());
            $serviceKernel->setEnvVariable(array(
                'host'          => $this->request->getHttpHost(),
                'schemeAndHost' => $this->request->getSchemeAndHttpHost(),
                'basePath'      => $this->request->getBasePath(),
                'baseUrl'       => $this->request->getSchemeAndHttpHost() . $this->request->getBasePath()))
                ->setTranslatorEnabled(true)
                ->setTranslator($container->get('translator'))
                ->setParameterBag($container->getParameterBag())
                ->registerModuleDirectory(dirname(__DIR__) . '/plugins')
                ->setConnection($biz['db']);

            $serviceKernel->getConnection()->exec('SET NAMES UTF8');

            $currentUser = new CurrentUser();
            $currentUser->fromArray(array(
                'id'        => 0,
                'nickname'  => '游客',
                'currentIp' => $this->request->getClientIp(),
                'roles'     => array()
            ));
            $serviceKernel->setCurrentUser($currentUser);
            $this->isServiceKernelInit = true;
        }
    }
}
