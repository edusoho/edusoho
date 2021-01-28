<?php

namespace Topxia\MobileBundleV2\Filter;

class Filter
{
    public $filterUrl = '';

    public function invoke()
    {
    }

    public function next($array = array())
    {
        return new FilterResult(true, false, $array);
    }

    public function dap($array = array())
    {
        return new FilterResult(false, false, $array);
    }

    public function stop($array = array())
    {
        return new FilterResult(false, true, $array);
    }
}
