<?php

namespace Biz\MultiClass\Service\Impl;

use Biz\BaseService;
use Biz\MultiClass\Dao\MultiClassProductDao;
use Biz\MultiClass\Service\MultiClassProductService;

class MultiClassProductServiceImpl extends BaseService implements MultiClassProductService
{
    /**
     * @return MultiClassProductDao
     */
    protected function getMultiClassProductDao()
    {
        return $this->createDao('MultiClass:MultiClassProductDao');
    }
}
