<?php

namespace AppBundle\Controller\Order;

use AppBundle\Controller\BaseController;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;

class OrderController extends BaseController
{
    public function showAction()
    {
        $product = $this->getProduct('course', array());

        $newProduct = $this->getOrderFacadeService()->show($product);
    }

    public function createAction()
    {
        $product = $this->getProduct('course', array());

        $order = $this->getOrderFacadeService()->create($product);
    }

    public function priceAction()
    {
        $product = $this->getProduct('course', array());

        $price = $this->getOrderFacadeService()->getPrice($product);
    }

    private function getProduct($targetType, $params)
    {
        $biz = $this->getBiz();

        /* @var $product Product */
        $product = $biz['order.product.'.$targetType];
        $product->setBiz($biz);

        $product->init($params);

        return $product;
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }
}
