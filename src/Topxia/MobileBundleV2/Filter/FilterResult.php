<?php

namespace Topxia\MobileBundleV2\Filter;

class FilterResult
{
    private $isNext;
    private $isFilter;
    public $resultData;

    public function __construct($isNext, $isFilter, $resultData)
    {
        $this->isNext = $isNext;
        $this->isFilter = $isFilter;
        $this->resultData = $resultData;
    }

    public function hasNext()
    {
        return $this->isNext;
    }

    public function hasFilter()
    {
        return $this->isFilter;
    }
}
