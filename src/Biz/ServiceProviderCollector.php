<?php

namespace Biz;

use Pimple\ServiceProviderInterface;

class ServiceProviderCollector
{
    protected $providers;

    public function __construct()
    {
        $this->providers = array();
    }

    public function add(ServiceProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    public function all()
    {
        return $this->providers;
    }
}
