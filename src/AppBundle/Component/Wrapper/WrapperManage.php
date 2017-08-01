<?php

namespace AppBundle\Component\Wrapper;

class WrapperManage
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function handle($object, $type)
    {
        $type = explode('.', $type);
        $Wrap = __NAMESPACE__.'\\'.ucfirst(array_shift($type).'Wrapper');
        if (empty($Wrap)) {
            return $object;
        }
        $wrap = new $Wrap($this->container);

        return $wrap->handle($object, array_shift($type));
    }
}
