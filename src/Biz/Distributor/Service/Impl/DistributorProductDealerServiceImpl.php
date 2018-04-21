<?php

namespace Biz\Distributor\Service\Impl;

use Biz\OrderFacade\Service\ProductDealerService;
use Biz\OrderFacade\Product\Product;
use Biz\Distributor\Util\DistributorCookieToolkit;
use Biz\Distributor\Util\DistributorUtil;
use Biz\BaseService;

class DistributorProductDealerServiceImpl extends BaseService implements ProductDealerService
{
    public function setParams($cookies = array())
    {
        $this->cookies = $cookies;
    }

    public function dealBeforeCreateProduct(Product $product)
    {
        $cookieName = DistributorCookieToolkit::getCookieName(DistributorCookieToolkit::PRODUCT_ORDER);
        if (!empty($this->cookies[$cookieName])) {
            $distributorToken = $this->cookies[$cookieName];
            $service = DistributorUtil::getDistributorServiceByToken($this->biz, $distributorToken);
            $splitedToken = $service->decodeToken($distributorToken);
            if ($splitedToken['valid']) {
                $product->setCreateExtra(
                    array('distributorToken' => $distributorToken)
                );
            }
        }

        return $product;
    }
}
