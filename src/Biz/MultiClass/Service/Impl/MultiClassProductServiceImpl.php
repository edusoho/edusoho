<?php

namespace Biz\MultiClass\Service\Impl;

use Biz\BaseService;
use Biz\MultiClass\Dao\MultiClassProductDao;
use Biz\MultiClass\Service\MultiClassProductService;

class MultiClassProductServiceImpl extends BaseService implements MultiClassProductService
{
    public function getProductByTitle($title)
    {
        return $this->getMultiClassProductDao()->getByTitle($title);
    }

    public function createProduct($product)
    {
        return $this->getMultiClassProductDao()->create($product);
    }

    public function searchProducts(array $conditions, array $oderBy, $start, $limit)
    {
        return $this->getMultiClassProductDao()->search($conditions, $oderBy, $start, $limit);
    }

    public function countProducts(array $conditions)
    {
        return $this->getMultiClassProductDao()->count($conditions);
    }

    /**
     * @return MultiClassProductDao
     */
    protected function getMultiClassProductDao()
    {
        return $this->createDao('MultiClass:MultiClassProductDao');
    }
}
