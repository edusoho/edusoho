<?php

namespace ApiBundle\Security\Authentication;

use ApiBundle\Api\Resource\ResourceProxy;
use ApiBundle\Event\ResourceEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class ResourceAuthenticationProviderManager implements ResourceAuthenticationInterface
{
    private $providers;

    private $container;

    public function __construct(ContainerInterface $container, array $providers)
    {
        $this->container = $container;
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
        $this->container->get('event_dispatcher')->dispatch(AuthenticationEvents::BEFORE_AUTHENTICATE, new AuthenticationEvent($this->container->get('security.token_storage')->getToken()));

        $lastException = null;
        $result = null;

        foreach ($this->providers as $provider) {
            $provider->authenticate($resourceProxy, $method);
        }

        $this->container->get('event_dispatcher')->dispatch(AuthenticationEvents::AFTER_AUTHENTICATE, new ResourceEvent($this->container->get('request_stack')->getMasterRequest(), $resourceProxy));
    }
}
