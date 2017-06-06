<?php

namespace ApiBundle\Api\Resource;

use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\PathMeta;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
            throw new BadRequestHttpException('API Resource Not found', null, ErrorCode::BAD_REQUEST);
        }

        return new ResourceProxy($this->container->get('api.field.filter.factory'), new $className($this->container));
    }
}