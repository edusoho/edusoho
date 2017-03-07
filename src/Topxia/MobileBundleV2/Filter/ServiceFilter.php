<?php

namespace Topxia\MobileBundleV2\Filter;

class ServiceFilter extends Filter
{
    public $filterUrl = '/.+/';

    public function invoke()
    {
        return $this->next();
    }
}
