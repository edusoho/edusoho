<?php

namespace Biz\Wrap;

class BaseWrap
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }
}
