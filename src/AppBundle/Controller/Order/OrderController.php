<?php

namespace AppBundle\Controller\Order;

use AppBundle\Controller\BaseController;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends BaseController
{
    public function showAction(Request $request)
    {
        $targetType = $request->query->get('targetType');
        $fields = $request->query->all();

        $product = $this->getProduct($targetType, $fields);
        $product = $this->getOrderFacadeService()->show($product);

        return $this->render('order/show/index.html.twig', array(
            'product' => $product,
        ));
    }

    public function createAction(Request $request)
    {
        $product = $this->getProduct($request->request->get('targetType'), $request->request->all());

        $order = $this->getOrderFacadeService()->create($product);

        return $this->redirectSafely($this->generateUrl('pay_center_show', array(
            'sn' => $order['sn'],
        )));
    }

    public function priceAction(Request $request)
    {
        $targetType = $request->query->get('targetType');
        $fields = $request->query->all();

        $product = $this->getProduct($targetType, $fields);
        
        $price = $this->getOrderFacadeService()->getPrice($product);

        return $this->createJsonResponse($price);
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

    public function couponCheckAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $code = trim($request->request->get('code'));
            $id = $request->request->get('targetId');
            $type = $request->request->get('targetType');

            $coupon = $this->getCouponService()->checkCoupon($code, $id, $type);
            return $this->createJsonResponse($coupon);
        }
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }

    /**
     * @return CouponService
     */
    protected function getCouponService()
    {
        return $this->getBiz()->service('Coupon:CouponService');
    }

    protected function getCashAccountService()
    {
        return $this->getBiz()->service('Cash:CashAccountService');
    }
}
