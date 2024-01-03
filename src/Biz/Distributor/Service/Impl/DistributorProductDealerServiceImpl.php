<?php

namespace Biz\Distributor\Service\Impl;

use Biz\BaseService;
use Biz\Distributor\Util\DistributorCookieToolkit;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\ProductDealerService;

class DistributorProductDealerServiceImpl extends BaseService implements ProductDealerService
{
    public function setParams($cookies = [])
    {
        $this->cookies = $cookies;
    }

    public function dealBeforeCreateProduct(Product $product)
    {
        $user = $this->getCurrentUser();
        $cookieName = DistributorCookieToolkit::getCookieName(DistributorCookieToolkit::PRODUCT_ORDER);
        $distributorToken = !empty($this->cookies[$cookieName]) ? $this->cookies[$cookieName] : $user['distributorToken'];
        if (empty($distributorToken) || !$this->isPluginInstalled('Drp')) {
            return $product;
        }
        $this->getLogger()->info('distributor start order sign valid DistributorProductDealerServiceImpl::dealBeforeCreateProduct', [
            'distributorToken' => $distributorToken,
        ]);
        $tokenInfo['valid'] = true;
        if (!empty($this->cookies[$cookieName])) {
            $tokenInfo = $this->getDistributorUserService()->decodeToken($distributorToken);
        }
        if ($tokenInfo['valid']) {
            $this->getLogger()->info('distributor order sign valid success DistributorProductDealerServiceImpl::dealBeforeCreateProduct', [
                'distributorToken' => $distributorToken,
            ]);
            $product->setCreateExtra(
                ['distributorToken' => $distributorToken]
            );
            $this->getDrpUserService()->trySaveUserDistributorToken($user['id'], $distributorToken);
        }

        return $product;
    }

    protected function getLogger()
    {
        return $this->biz->offsetGet('drp.plugin.logger');
    }

    protected function getDistributorUserService()
    {
        return $this->createService('Distributor:DistributorUserService');
    }

    /**
     * @return \DrpPlugin\Biz\Drp\Service\DrpUserService
     */
    protected function getDrpUserService()
    {
        return $this->createService('DrpPlugin:Drp:DrpUserService');
    }
}
