<?php

namespace AppBundle\Controller\Callback;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ProcessorFactory
{
    private $container;

    private $pool = array();

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getClassMap($type)
    {
        $namespace = __NAMESPACE__;
        $classMap = array(
            'cloud_search' => $namespace.'\\CloudSearch\\CloudSearchProcessor',
        );

        if (!isset($classMap[$type])) {
            throw new \InvalidArgumentException(sprintf('Processor not available: %s', $type));
        }

        return $classMap[$type];
    }

    public function create($type)
    {
        if (empty($this->pool[$type])) {
            $class = $this->getClassMap($type);
            $instance = new $class();
            $instance->setContainer($this->container);
            $this->pool[$type] = $instance;
        }

        return $this->pool[$type];
    }
}
