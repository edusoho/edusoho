<?php

namespace Biz\Product\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Product\Dao\ProductDao;
use Biz\Product\Service\ProductService;

class ProductServiceImpl extends BaseService implements ProductService
{
    public function getProduct($id)
    {
        return $this->getProductDao()->get($id);
    }

    public function createProduct($product)
    {
        if (!ArrayToolkit::requireds($product, ['targetType', 'targetId', 'title'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $product = ArrayToolkit::parts($product, ['targetType', 'targetId', 'title']);

        $product['owner'] = $this->getCurrentUser()->getId();

        return $this->getProductDao()->create($product);
    }

    public function updateProduct($id, $product)
    {
        $product = ArrayToolkit::parts($product, ['title']);

        return $this->getProductDao()->update($id, $product);
    }

    public function deleteProduct($id)
    {
        return $this->getProductDao()->delete($id);
    }

    public function searchProducts($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getProductDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function getProductByTargetIdAndType($targetId, $targetType)
    {
        return $this->getProductDao()->getByTargetIdAndType($targetId, $targetType);
    }

    public function findProductsByIds($ids)
    {
        return ArrayToolkit::index($this->getProductDao()->findByIds($ids), 'id');
    }

    /**
     * @return ProductDao
     */
    protected function getProductDao()
    {
        return $this->createDao('Product:ProductDao');
    }
}
