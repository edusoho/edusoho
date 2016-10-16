<?php

namespace Biz;

use Codeages\Biz\Framework\Context\Biz as BizBase;
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
class Biz extends BizBase
{
    protected $container;

    protected $serviceKernelBooted = false;

    public function __construct(ContainerInterface $container, array $config)
    {
        $this->container = $container;
        parent::__construct($config);
        $this->boot();
    }

    public function boot($options = array())
    {
        $this->put('migration_directories', dirname(dirname(__DIR__)).'/migrations');
        parent::boot($options);
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