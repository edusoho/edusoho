<?php

use AppBundle\Common\ExtensionManager;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Provider\DoctrineServiceProvider;
use Codeages\Biz\Framework\Provider\MonologServiceProvider;
use Codeages\PluginBundle\System\PluginableHttpKernelInterface;
use Codeages\PluginBundle\System\PluginConfigurationManager;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use Topxia\Service\Common\ServiceKernel;

class AppKernel extends Kernel implements PluginableHttpKernelInterface
{
    protected $plugins = array();

    /**
     * @var Request
     */
    protected $request;

    protected $extensionManger;

    private $isServiceKernelInit = false;

    protected $pluginConfigurationManager;

    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);
        date_default_timezone_set('Asia/Shanghai');
        $this->extensionManger = ExtensionManager::init($this);
        $this->pluginConfigurationManager = new PluginConfigurationManager($this->getRootDir());
    }

    public function boot()
    {
        if (true === $this->booted) {
            return;
        }

        if ($this->loadClassCache) {
            $this->doLoadClassCache($this->loadClassCache[0], $this->loadClassCache[1]);
        }

        // init bundles
        $this->initializeBundles();

        // init container
        $this->initializeContainer();

        $this->initializeBiz($this->getContainer()->get('biz'));
        $this->initializeServiceKernel();
        foreach ($this->getBundles() as $bundle) {
            $bundle->setContainer($this->container);
            $bundle->boot();
        }

        $this->booted = true;
    }

    public function registerBundles()
    {
        $bundles = array(
            new Codeages\PluginBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Endroid\QrCode\Bundle\EndroidQrCodeBundle(),
            new Topxia\WebBundle\TopxiaWebBundle(),
            new Topxia\AdminBundle\TopxiaAdminBundle(),
            new Topxia\MobileBundleV2\TopxiaMobileBundleV2(),
            new Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle(),
            new OAuth2\ServerBundle\OAuth2ServerBundle(),
            new Codeages\PluginBundle\CodeagesPluginBundle(),
            new AppBundle\AppBundle(),
            new CustomBundle\CustomBundle(),
            new ApiBundle\ApiBundle(),
        );

        if (is_file($this->getRootDir().'/config/sentry.yml')) {
            $bundles[] = new Sentry\SentryBundle\SentryBundle();
        }

        if ('test' !== $this->getEnvironment()) {
            $plugins = $this->pluginConfigurationManager->getInstalledPlugins();

            foreach ($plugins as $plugin) {
                if ('plugin' != $plugin['type']) {
                    continue;
                }

                if (3 != $plugin['protocol']) {
                    continue;
                }

                $code = ucfirst($plugin['code']);
                $class = "{$code}Plugin\\{$code}Plugin";
                $bundles[] = new $class();
            }
        }

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            if (class_exists('Symfony\Bundle\WebProfilerBundle\WebProfilerBundle')) {
                $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            }
            if (class_exists('Sensio\Bundle\DistributionBundle\SensioDistributionBundle')) {
                $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            }
            if (class_exists('Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle')) {
                $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            }
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    public function getPlugins()
    {
        return $this->pluginConfigurationManager->getInstalledPlugins();
    }

    public function getPluginConfigurationManager()
    {
        return $this->pluginConfigurationManager;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    public function initializeBiz(Biz $biz)
    {
        $biz['migration.directories'][] = dirname(__DIR__).'/migrations';
        $biz['env'] = array(
            'base_url' => $this->request->getSchemeAndHttpHost().$this->request->getBasePath(),
        );
        $biz['activity_dir'] = $this->getContainer()->getParameter('edusoho.activities_dir');

        $biz->register(new DoctrineServiceProvider());
        $biz->register(new MonologServiceProvider(), array(
            'monolog.logfile' => $this->getContainer()->getParameter('kernel.logs_dir').'/biz.log',
            'monolog.level' => $this->isDebug() ? \Monolog\Logger::DEBUG : \Monolog\Logger::INFO,
            'monolog.permission' => 0666,
        ));
        $biz->register(new \Codeages\Biz\Framework\Provider\SchedulerServiceProvider());
        $biz->register(new \Codeages\Biz\Framework\Provider\TargetlogServiceProvider());
        $biz->register(new \Biz\DefaultServiceProvider());

        $collector = $this->getContainer()->get('biz.service_provider.collector');
        foreach ($collector->all() as $provider) {
            $biz->register($provider);
        }

        $biz->register(new Codeages\Biz\RateLimiter\RateLimiterServiceProvider());
        $this->registerCacheServiceProvider($biz);
        $biz->register(new Codeages\Biz\Order\OrderServiceProvider());
        $biz->register(new Codeages\Biz\Pay\PayServiceProvider());

        $biz->register(new \Biz\Accessor\AccessorServiceProvider());
        $biz->register(new \Biz\OrderFacade\OrderFacadeServiceProvider());
        $biz->register(new \Biz\Xapi\XapiServiceProvider());
        $biz->register(new \Codeages\Biz\Framework\Provider\SessionServiceProvider());
        $biz->register(new \Codeages\Biz\Framework\Provider\QueueServiceProvider());
        $biz->boot();

        $activeTheme = $this->pluginConfigurationManager->getActiveThemeName();
        if (empty($activeTheme)) {
            $this->pluginConfigurationManager->setActiveThemeName('jianmo')->save();
        }
        $biz['pluginConfigurationManager'] = $this->pluginConfigurationManager;
    }

    protected function registerCacheServiceProvider($biz)
    {
        if ($this->getContainer()->hasParameter('redis_host')) {
            $biz->register(
                new Codeages\Biz\Framework\Provider\RedisServiceProvider(),
                array(
                    'redis.options' => array(
                        'host' => $this->getContainer()->getParameter('redis_host'),
                        'timeout' => $this->getContainer()->getParameter('redis_timeout'),
                        'reserved' => $this->getContainer()->getParameter('redis_reserved'),
                        'redis_interval' => $this->getContainer()->getParameter('redis_retry_interval'),
                    ),
                    'dao.cache.enabled' => true,
                )
            );
        }
    }

    protected function initializeServiceKernel()
    {
        if (!$this->isServiceKernelInit) {
            $container = $this->getContainer();
            $biz = $container->get('biz');

            $serviceKernel = ServiceKernel::create($this->getEnvironment(), $this->isDebug());

            $currentUser = new \Biz\User\AnonymousUser($this->request->getClientIp() ?: '127.0.0.1');

            $biz['user'] = $currentUser;
            $serviceKernel
                ->setBiz($biz)
                ->setCurrentUser($currentUser)
                ->setEnvVariable(
                array(
                    'host' => $this->request->getHttpHost(),
                    'schemeAndHost' => $this->request->getSchemeAndHttpHost(),
                    'basePath' => $this->request->getBasePath(),
                    'baseUrl' => $this->request->getSchemeAndHttpHost().$this->request->getBasePath(),
                )
            )
                ->setTranslatorEnabled(true)
                ->setTranslator($container->get('translator'))
                ->setParameterBag($container->getParameterBag())
                ->registerModuleDirectory(dirname(__DIR__).'/plugins');

            $this->isServiceKernelInit = true;
        }
    }

    public function getCacheDir()
    {
        $theme = $this->pluginConfigurationManager->getActiveThemeName();
        $theme = empty($theme) ? '' : ucfirst(str_replace('-', '_', $theme));

        return $this->rootDir.'/cache/'.$this->environment.'/'.$theme;
    }
}
