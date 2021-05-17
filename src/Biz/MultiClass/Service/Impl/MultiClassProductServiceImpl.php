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

    public function getProductById($id)
    {
        return $this->getMultiClassProductDao()->get($id);
    }

    public function updateProductById($id, $fields)
    {
        return $this->getMultiClassProductDao()->update($id, $fields);
    }

    public function deleteProductById($id)
    {
        return $this->getMultiClassProductDao()->delete($id);
    }

    /**
     * @return MultiClassProductDao
     */
    protected function getMultiClassProductDao()
    {
        return $this->createDao('MultiClass:MultiClassProductDao');
    }
}
