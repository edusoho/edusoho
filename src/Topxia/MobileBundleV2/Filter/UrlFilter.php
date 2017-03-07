<?php

namespace Topxia\MobileBundleV2\Filter;

class UrlFilter extends Filter
{
    public $filterUrl = '/Course\\/getVersion/';

    public function invoke()
    {
        return $this->next();
    }
}
