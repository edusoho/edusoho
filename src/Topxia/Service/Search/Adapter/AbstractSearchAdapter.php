<?php

namespace Topxia\Service\Search\Adapter;

use Topxia\Service\Common\BaseService;

abstract class AbstractSearchAdapter extends BaseService
{
    abstract public function adapt(array $targets);

}
