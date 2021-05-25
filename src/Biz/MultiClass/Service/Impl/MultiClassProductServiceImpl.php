<?php

namespace Biz\MultiClass\Service\Impl;

use AppBundle\Common\ArrayToolkit;
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

    public function findProductByIds($ids)
    {
        return ArrayToolkit::index($this->getMultiClassProductDao()->findByIds($ids), 'id');
    }

    public function getProduct($id)
    {
        return $this->getMultiClassProductDao()->get($id);
    }

    public function getDefaultProduct()
    {
        return $this->getMultiClassProductDao()->getByType('default');
    }

    public function updateProduct($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, ['title', 'remark']);
        $product = $this->getMultiClassProductDao()->update($id, $fields);

        $this->getLogService()->info('multi_class_product', 'update_product', '更新', $product);

        return $product;
    }

    public function deleteProduct($id)
    {
        $result = $this->getMultiClassProductDao()->delete($id);

        $this->getLogService()->info('multi_class_product', 'delete_product', '删除', $id);

        return $result;
    }

    public function searchProducts(array $conditions, array $orderBy, $start, $limit, $columns = [])
    {
        return $this->getMultiClassProductDao()->search($conditions, $orderBy, $start, $limit, $columns);
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

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
