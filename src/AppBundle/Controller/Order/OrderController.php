<?php

namespace AppBundle\Controller\Order;

use AppBundle\Controller\BaseController;
use Biz\Coupon\Service\CouponService;
use Biz\Distributor\Util\DistributorCookieToolkit;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;
use Codeages\Biz\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends BaseController
{
    public function showAction(Request $request)
    {
        $product = $this->getProduct($request->query->get('targetType'), $request->query->all());

        $product->setAvailableDeduct();
        $product->setPickedDeduct([]);

        return $this->render('order/show/index.html.twig', [
            'product' => $product,
        ]);
    }

    public function createAction(Request $request)
    {
        $product = $this->getProduct($request->request->get('targetType'), $request->request->all());
        $product->setPickedDeduct($request->request->all());

        $this->addCreateDealers($request);

        $order = $this->getOrderFacadeService()->create($product);
        $response = $this->redirectSafely($this->generateUrl('cashier_show', [
            'sn' => $order['sn'],
        ]));

        $resonse = DistributorCookieToolkit::clearCookieToken(
            $request,
            $response,
            ['checkedType' => DistributorCookieToolkit::PRODUCT_ORDER]
        );

        return $response;
    }

    public function priceAction(Request $request)
    {
        $targetType = $request->query->get('targetType');
        $fields = $request->query->all();

        $product = $this->getProduct($targetType, $fields);
        $product->setPickedDeduct($fields);

        $priceFormat = $this->get('web.twig.order_extension')->priceFormat($product->getPayablePrice());
        $deducts = $product->getDeducts();

        return $this->createJsonResponse(
            [
                'price' => $product->getPayablePrice(),
                'priceFormat' => $priceFormat,
                'deducts' => $deducts,
            ]
        );
    }

    private function getProduct($targetType, $params)
    {
        $biz = $this->getBiz();

        /* @var $product Product */
        //todo 命名问题
        $product = $biz['order.product.'.$targetType];

        $product->init($params);

        return $product;
    }

    public function couponCheckAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $code = trim($request->request->get('code'));
            $id = $request->request->get('targetId');
            $type = $request->request->get('targetType');
            $price = $request->request->get('price');

            $user = $this->getCurrentUser();
            $limiter = $this->getRateLimiter('coupon_check_limit', 60, 3600);
            $maxAllowance = $limiter->getAllow($user['id']);
            if (0 == $maxAllowance) {
                $message = ['useable' => 'no', 'message' => '优惠码校验受限，请稍后尝试'];

                return $this->createJsonResponse($message);
            }

            $coupon = $this->getCouponService()->getCouponByCode($code);
            $batch = $this->getCouponBatchService()->getBatch($coupon['batchId']);
            $limiter->check($user['id']);
            if (empty($batch['codeEnable'])) {
                $message = ['useable' => 'no', 'message' => '该优惠券不存在'];

                return $this->createJsonResponse($message);
            }

            if (isset($batch['deadlineMode']) && 'day' == $batch['deadlineMode']) {
                //ES优惠券领取时，对于优惠券过期时间会加86400秒，所以计算deadline时对于固定天数模式应与设置有效期模式一致，都为当天凌晨00:00:00
                $fields['deadline'] = strtotime(date('Y-m-d')) + 24 * 60 * 60 * $batch['fixedDay'];

                $this->getCouponService()->updateCoupon($coupon['id'], $fields);
            }

            $coupon = $this->getCouponService()->checkCoupon($code, $id, $type);
            if (isset($coupon['useable']) && 'no' == $coupon['useable']) {
                return $this->createJsonResponse($coupon);
            }
            $coupon['deduct_amount'] = $this->getCouponService()->getDeductAmount($coupon, $price);
            $coupon['deduct_amount_format'] = $this->get('web.twig.order_extension')->priceFormat($coupon['deduct_amount']);

            return $this->createJsonResponse($coupon);
        }

        return $this->createAccessDeniedException();
    }

    public function detailAction(Request $request, $id)
    {
        $order = $this->getOrderService()->getOrder($id);

        preg_match('/管理员添加/', $order['title'], $order['edit']);
        $user = $this->getUserService()->getUser($order['user_id']);

        $orderLogs = $this->getOrderService()->findOrderLogsByOrderId($order['id']);

        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);

        $paymentTrade = $this->getPayService()->getTradeByTradeSn($order['trade_sn']);

        $orderDeducts = $this->getOrderService()->findOrderItemDeductsByOrderId($order['id']);

        $users = $this->getUserService()->findUsersByIds(array_column($orderLogs, 'user_id'));

        return $this->render('order/detail-modal.html.twig', [
            'order' => $order,
            'user' => $user,
            'orderLogs' => $orderLogs,
            'orderItems' => $orderItems,
            'paymentTrade' => $paymentTrade,
            'orderDeducts' => $orderDeducts,
            'users' => $users,
        ]);
    }

    protected function getRateLimiter($id, $maxAllowance, $period)
    {
        $factory = $this->getBiz()->offsetGet('ratelimiter.factory');

        return $factory($id, $maxAllowance, $period);
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

    /**
     * @return \Codeages\Biz\Order\Service\OrderService
     */
    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    /**
     * @return PayService
     */
    protected function getPayService()
    {
        return $this->createService('Pay:PayService');
    }

    protected function getDistributorProductDealder()
    {
        return $this->createService('Distributor:DistributorProductDealerService');
    }

    private function addCreateDealers(Request $request)
    {
        $serviceNames = ['Distributor:DistributorProductDealerService', 'S2B2C:S2B2CProductDealerService'];

        foreach ($serviceNames as $serviceName) {
            $service = $this->createService($serviceName);
            $service->setParams($request->cookies->all());
            $this->getOrderFacadeService()->addDealer($service);
        }
    }

    //插件service
    protected function getCouponBatchService()
    {
        return $this->createService('Coupon:CouponBatchService');
    }
}
