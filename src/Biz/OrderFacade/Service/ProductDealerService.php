<?php

namespace Biz\OrderFacade\Service;

use Biz\OrderFacade\Product\Product;

interface ProductDealerService
{
    /**
     * @param $cookies 可供 dealBeforeCreateProduct 使用
     */
    public function setParams($cookies = array());

    /**
     * @return 处理过的 $product
     */
    public function dealBeforeCreateProduct(Product $product);
}
