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
        $price = $this->get('web.twig.app_extension')->priceFormat($price);
        
        return $this->createJsonResponse($price);
    }

    private function getProduct($targetType, $params)
    {
        $biz = $this->getBiz();

        /* @var $product Product */
        //todo 命名问题
        $product = $biz['order.product.'.$targetType];
        //biz constuctor
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
            $price = $request->request->get('price');
            $coupon = $this->getCouponService()->checkCoupon($code, $id, $type);
            if (isset($coupon['useable']) && $coupon['useable'] == 'no') {
                return $this->createJsonResponse($coupon);
            }

            if ($coupon['type'] == 'minus') {
                $coupon['deduct_amount'] = $coupon['rate'];
            } else {
                $coupon['deduct_amount'] = round($price * ($coupon['rate'] / 10), 2);
            }
            $coupon['deduct_amount_format'] = $this->get('web.twig.app_extension')->priceFormat($coupon['deduct_amount']);

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
