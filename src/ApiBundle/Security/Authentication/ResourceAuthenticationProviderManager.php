<?php

namespace ApiBundle\Security\Authentication;

use ApiBundle\Api\Resource\ResourceProxy;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
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

        $this->container->get('event_dispatcher')->dispatch(AuthenticationEvents::AFTER_AUTHENTICATE, new GetResponseEvent($this->container->get('kernel'), $this->container->get('request'), HttpKernelInterface::MASTER_REQUEST));
    }

}