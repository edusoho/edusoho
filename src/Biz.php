<?php

/**
 * User: retamia
 * Date: 2016/10/12
 * Time: 18:07
 */
class Biz extends \Codeages\Biz\Framework\Context\Kernel
{

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->boot();
    }

    /**
     * @return \Pimple\ServiceProviderInterface[]
     */
    public function registerProviders()
    {
        // 需要插件机制去实现 provider
        return array(

        );
    }
}