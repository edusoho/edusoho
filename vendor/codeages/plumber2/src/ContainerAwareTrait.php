<?php

namespace Codeages\Plumber;

use Psr\Container\ContainerInterface;

/**
 * Basic Implementation of ContainerAwareInterface.
 */
trait ContainerAwareTrait
{
    /**
     * The container instance.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Sets a container.
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
