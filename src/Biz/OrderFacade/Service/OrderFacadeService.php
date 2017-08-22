<?php

namespace Biz\OrderFacade\Service;


use Biz\OrderFacade\Product\Product;

interface OrderFacadeService
{
    public function show(Product $product);

    public function getPrice(Product $product);

    public function create(Product $product);
}