<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Bundle\AsseticBundle\Config;

use Assetic\Factory\Resource\ResourceInterface as AsseticResourceInterface;
use Symfony\Component\Config\Resource\ResourceInterface as SymfonyResourceInterface;

/**
 * Turns an Assetic resource into a Symfony one.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class AsseticResource implements SymfonyResourceInterface
{
    private $resource;

    public function __construct(AsseticResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    public function __toString()
    {
        return (string) $this->resource;
    }

    public function isFresh($timestamp)
    {
        return $this->resource->isFresh($timestamp);
    }

    /**
     * Returns the Assetic resource.
     *
     * @return AsseticResourceInterface The wrapped Assetic resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    public function exists()
    {
        return true;
    }

    public function getId()
    {
        return md5('assetic'.$this->resource);
    }

    public function getModificationTime()
    {
        return -1;
    }

    public function serialize()
    {
        return serialize($this->resource);
    }

    public function unserialize($serialized)
    {
        $this->resource = unserialize($serialized);
    }
}
