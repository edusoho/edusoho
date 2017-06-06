<?php

namespace ApiBundle\Security\Authentication;

use ApiBundle\Api\Resource\ResourceProxy;

class ResourceAuthenticationProviderManager implements ResourceAuthenticationInterface
{
    private $providers;

    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    public function addProvider($provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(ResourceProxy $resourceProxy, $method)
    {
        $lastException = null;
        $result = null;

        foreach ($this->providers as $provider) {
            $provider->authenticate($resourceProxy, $method);
        }
    }

}