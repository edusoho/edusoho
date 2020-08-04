<?php

namespace Biz\Goods\Entity;

use Biz\Product\ProductException;
use Biz\Product\Service\ProductService;
use Codeages\Biz\Framework\Context\Biz;

/**
 * Class BaseGoodsEntity
 */
abstract class BaseGoodsEntity
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    abstract public function getTarget($goods);

    abstract public function canManageTarget($goods);

    abstract public function fetchTargets($goodses);

    /**
     * @param $productId
     *
     * @return mixed
     */
    protected function getProduct($productId)
    {
        $product = $this->getProductService()->getProduct($productId);
        if (empty($product)) {
            throw ProductException::NOTFOUND_PRODUCT();
        }

        return $product;
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->biz->service('Product:ProductService');
    }
}
