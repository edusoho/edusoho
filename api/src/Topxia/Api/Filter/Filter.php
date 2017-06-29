<?php

namespace Topxia\Api\Filter;

interface Filter
{
    public function filter(array &$data);

    public function filters(array &$datas);
}