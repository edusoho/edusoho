<?php

namespace Biz\Search\Adapter;

use Biz\BaseService;

abstract class AbstractSearchAdapter extends BaseService
{
    abstract public function adapt(array $targets);
}
