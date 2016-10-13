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

    public function boot($options = array())
    {
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