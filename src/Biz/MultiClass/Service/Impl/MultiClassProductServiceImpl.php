<?php

namespace Biz\MultiClass\Service\Impl;

use Biz\BaseService;
use Biz\MultiClass\Dao\MultiClassProductDao;
use Biz\MultiClass\Service\MultiClassProductService;
use Biz\System\Service\LogService;

class MultiClassProductServiceImpl extends BaseService implements MultiClassProductService
{
    public function getProductByTitle($title)
    {
        return $this->getMultiClassProductDao()->getByTitle($title);
    }

    public function createProduct($product)
    {
        $product = $this->getMultiClassProductDao()->create($product);

        $this->getLogService()->info('multi_class_product', 'create_product', '新增', $product);

        return $product;
    }

    public function getProduct($id)
    {
        return $this->getMultiClassProductDao()->get($id);
    }

    public function updateProduct($id, $fields)
    {
        return $this->getMultiClassProductDao()->update($id, $fields);
    }

    public function deleteProductById($id)
    {
        $result = $this->getMultiClassProductDao()->delete($id);

        $this->getLogService()->info('multi_class_product', 'delete_product', '删除', $id);

        return $result;
    }

    /**
     * @return MultiClassProductDao
     */
    protected function getMultiClassProductDao()
    {
        return $this->createDao('MultiClass:MultiClassProductDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
