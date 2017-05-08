<?php

namespace ApiBundle\Api\Resource;

use ApiBundle\Api\Exception\ApiNotFoundException;
use ApiBundle\Api\PathMeta;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ResourceManager
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(PathMeta $meta)
    {
        $className = $meta->getResourceClassName();

        if (!class_exists($className)) {
            throw new ApiNotFoundException('API Resource Not found');
        }
        return new ResourceProxy($this->container->get('api.field.filter.factory'), new $className($this->container));
    }
}