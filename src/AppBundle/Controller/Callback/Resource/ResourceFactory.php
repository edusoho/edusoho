<?php

namespace AppBundle\Controller\Callback\Resource;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ResourceFactory
{
    private $container;

    private $pool = array();

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create($resouce)
    {
        if (empty($this->pool[$resouce])) {
            $class = ResourceMap::getClass($resouce);
            $this->pool[$resouce] = new $class($this->container);
        }

        return $this->pool[$resouce];
    }
}