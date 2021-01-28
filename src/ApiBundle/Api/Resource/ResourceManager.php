<?php

namespace ApiBundle\Api\Resource;

use ApiBundle\Api\PathMeta;
use Biz\Common\CommonException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ResourceManager
{
    private $container;

    private $customApiNamespaces = array();

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(PathMeta $meta)
    {
        $className = $this->getClassName($meta);
        if (!class_exists($className)) {
            throw CommonException::NOTFOUND_API();
        }

        return new ResourceProxy($this->container->get('api.field.filter.factory'), new $className($this->container, $this->container->get('biz')));
    }

    /**
     * 复写的API优先查找
     *
     * @param PathMeta $meta
     *
     * @return string
     */
    private function getClassName(PathMeta $meta)
    {
        $overrideFindInCustom = $meta->fallbackToCustomApi($this->customApiNamespaces);
        if ($overrideFindInCustom['isFind']) {
            return $overrideFindInCustom['className'];
        } else {
            return $meta->getResourceClassName();
        }
    }

    public function registerApi($namespace)
    {
        $this->customApiNamespaces[] = $namespace;
    }
}
