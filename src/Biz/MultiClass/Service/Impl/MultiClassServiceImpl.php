<?php

namespace Biz\MultiClass\Service\Impl;

use Biz\BaseService;
use Biz\MultiClass\Dao\MultiClassDao;
use Biz\MultiClass\Service\MultiClassService;

class MultiClassServiceImpl extends BaseService implements MultiClassService
{
    public function findByProductId($productId)
    {
        return $this->getMultiClassDao()->findByProductId($productId);
    }

    public function getMultiClassByTitle($title)
    {
        return $this->getMultiClassDao()->getByTitle($title);
    }

    /**
     * @return MultiClassDao
     */
    protected function getMultiClassDao()
    {
        return $this->createDao('MultiClass:MultiClassDao');
    }
}
