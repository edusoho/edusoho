<?php

namespace Topxia\MobileBundleV2\Processor;

class ProcessorDelegator
{
    private $target;
    private $invokeArray;

    public function __construct($target)
    {
        $this->invokeArray = array();
        $this->target = $target;
    }

    public function __call($name, $arguments)
    {
        if (!method_exists($this->target, $name)) {
            return array('error' => 'method not exists');
        }
        if (method_exists($this, $name) || $this->filterMethod($name)) {
            return array('error' => 'the method is serviceDelegator');
        }

        return $this->invokeFunction($name, $arguments);
    }

    public function stopInvoke()
    {
        $this->invokeArray = array();
    }

    private function invokeFunction($name, $arguments)
    {
        $functionResult = array();
        array_push($this->invokeArray, 'before', $name, 'after');
        while ($function = array_pop($this->invokeArray)) {
            $result = call_user_func(array($this->target, $function), $arguments);
            if ($result != null) {
                $functionResult = $result;
            }
        }

        return $functionResult;
    }

    private $methodFilters = array(
        '__construct',
        'after',
        'before',
    );

    private function filterMethod($method)
    {
        return in_array($method, $this->methodFilters);
    }
}
