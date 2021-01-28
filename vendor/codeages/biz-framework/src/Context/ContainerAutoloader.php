<?php

namespace Codeages\Biz\Framework\Context;

use Pimple\Container;

class ContainerAutoloader
{
    protected $container;

    protected $aliases;

    protected $makers;

    public function __construct(Container $container, \ArrayObject $aliases, array $makers)
    {
        $this->container = $container;
        $this->aliases = $aliases;
        $this->makers = $makers;
    }

    public function autoload($makerName, $alias)
    {
        $parts = explode(':', $alias);
        if (empty($parts)) {
            throw new \InvalidArgumentException('Service alias parameter is invalid.');
        }

        if (isset($this->container["@{$alias}"])) {
            return $this->container["@{$alias}"];
        }

        if (1 === count($parts)) {
            $prefix = '';
            $middle = array();
            $name = $parts[0];
        } else {
            $prefix = $parts[0];
            $middle = array_slice($parts, 1, -1);
            $name = end($parts);
        }

        if (!isset($this->aliases[$prefix])) {
            $middle = array_merge(array($prefix), $middle);
            $prefix = '';
        }
        $namespace = rtrim($this->aliases[$prefix], '\\');

        $middle = implode('\\', $middle);
        if ($middle) {
            $namespace .= '\\'.$middle;
        }

        $maker = $this->makers[$makerName];

        $obj = $maker($namespace, $name);

        $this->container["@{$alias}"] = $obj;

        return $obj;
    }
}
