<?php

namespace Codeages\Plumber;

use Psr\Container\ContainerInterface;

/**
 * Describes a psr-container-aware instance.
 */
interface ContainerAwareInterface
{
    /**
     * Sets a process instance on the object.
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container);
}
