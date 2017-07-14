<?php

namespace Biz\Wrap;

class Wrap
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }


    public function handle($object, $type)
    {
        $type = explode('.', $type);
        $Wrap =__NAMESPACE__. '\\'. ucfirst(array_shift($type). 'Wrap');
        if (empty($Wrap)) {
            return $object;
        }
        $wrap = new $Wrap($this->container);

        return $wrap->handle($object, array_shift($type));
    }
}
