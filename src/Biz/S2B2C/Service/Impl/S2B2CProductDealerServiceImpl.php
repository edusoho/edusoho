<?php

namespace Biz\S2B2C\Service\Impl;

use Biz\BaseService;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\ProductDealerService;
use Biz\S2B2C\Service\ProductService;

/**
 * Class S2B2CProductDealerServiceImpl
 *
 * @codeCoverageIgnore
 */
class S2B2CProductDealerServiceImpl extends BaseService implements ProductDealerService
{
    public $involvedProductTypes = ['course'];

    public function setParams($params = [])
    {
    }

    public function dealBeforeCreateProduct(Product $product)
    {
        if (!in_array($product->targetType, $this->involvedProductTypes)) {
            return $product;
        }
        $s2b2cProduct = $this->getS2b2cProductService()->getByTypeAndLocalResourceId($product->targetType, $product->targetId);
        if (empty($s2b2cProduct)) {
            return $product;
        }
        $product->setCreateExtra(['s2b2cProductDetailId' => $s2b2cProduct['s2b2cProductDetailId']]);

        return $product;
    }

    /**
     * @return ProductService
     */
    protected function getS2b2cProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }
}
