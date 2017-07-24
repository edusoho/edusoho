<?php

namespace AppBundle\Component\Wrapper;

abstract class Wrapper
{
    protected $container;

    abstract protected function getWrapList();

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function handle($object, $function = '')
    {
        if (empty($function)) {
            $list = $this->getWrapList();
        } else {
            $list = array($function);
        }

        foreach ($list as $item) {
            $object = $this->execute($object, $item);
        }

        return $object;
    }

    private function execute($object, $function)
    {
        $isCallAble = is_callable(array($this, $function));
        if ($isCallAble) {
            $object = call_user_func(array($this, $function), $object);
        }

        return $object;
    }
}
