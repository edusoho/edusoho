<?php

namespace ApiBundle;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Viewer
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function view($result)
    {
        $request = $this->container->get('request');
        $isEnvelop = $request->query->get('envelope', false);

        $request->headers->get();
    }
}
