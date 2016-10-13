<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\TopxiaServiceProvider;

/**
 * User: retamia
 * Date: 2016/10/12
 * Time: 18:07
 */
class Biz extends \Codeages\Biz\Framework\Context\Kernel
{
    protected $container;
    protected $serviceKernelBooted = false;

    public function __construct(ContainerInterface $container, array $config)
    {
        $this->container = $container;
        parent::__construct($config);
        $this->boot();
    }

    public function bootServiceKernel(Request $request)
    {
        if(!$this->serviceKernelBooted){
            $kernel = $this->container->get('kernel');
            $serviceKernel = ServiceKernel::create($kernel->getEnvironment(), $kernel->isDebug());
            $serviceKernel->setEnvVariable(array(
                'host'          => $request->getHttpHost(),
                'schemeAndHost' => $request->getSchemeAndHttpHost(),
                'basePath'      => $request->getBasePath(),
                'baseUrl'       => $request->getSchemeAndHttpHost().$request->getBasePath()
            ));
            $serviceKernel->setTranslatorEnabled(true);
            $serviceKernel->setTranslator($this->container->get('translator'));
            $serviceKernel->setParameterBag($this->container->getParameterBag());
            $serviceKernel->registerModuleDirectory(dirname(__DIR__).'/plugins');
            $serviceKernel->setConnection($kernel->getContainer()->get('database_connection'));
            $serviceKernel->getConnection()->exec('SET NAMES UTF8');

            $currentUser = new CurrentUser();
            $currentUser->fromArray(array(
                'id'        => 0,
                'nickname'  => '游客',
                'currentIp' => $request->getClientIp(),
                'roles'     => array()
            ));
            $serviceKernel->setCurrentUser($currentUser);

            $this->serviceKernelBooted = true;
        }
    }

    /**
     * @return \Pimple\ServiceProviderInterface[]
     */
    public function registerProviders()
    {
        // 需要插件机制去实现 provider
        return array(
            new TopxiaServiceProvider()
        );
    }


}