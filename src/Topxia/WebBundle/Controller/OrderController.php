<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Component\Payment\Payment;
use Topxia\Service\Order\OrderProcessor\OrderProcessorFactory;

class OrderController extends BaseController
{
    public function showAction(Request $request) 
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isLogin()) {
            return $this->redirect($this->generateUrl('login'));
        }

        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');

        if(empty($targetType) || empty($targetId) || !in_array($targetType, array("course", "vip")) ) {
            return $this->createMessageResponse('error', '参数不正确');
        }
        
        $processor = OrderProcessorFactory::create($targetType);

        $fields = $request->query->all();
        $orderInfo = $processor->getOrderInfo($targetId, $fields);

        if($orderInfo["totalPrice"] == 0){
            $formData = array();
            $formData['userId'] = $currentUser["id"];
            $formData["targetId"] = $fields["targetId"];
            $formData["targetType"] = $fields["targetType"];
            $formData['amount'] = 0;
            $formData['totalPrice'] = 0;
            $coinSetting = $this->setting("coin");
            $formData['priceType'] = empty($coinSetting["priceType"])?'RMB':$coinSetting["priceType"];
            $formData['coinRate'] = empty($coinSetting["coinRate"])?1:$coinSetting["coinRate"];
            $formData['coinAmount'] = 0;
            $formData['payment'] = 'alipay';
            $order = $processor->createOrder($formData, $fields);

            if ($order['status'] == 'paid') {
                return $this->redirect($this->generateUrl($processor->getRouter(), array('id' => $order['targetId'])));
            }
        }

        return $this->render('TopxiaWebBundle:Order:order-create.html.twig', $orderInfo);

    }

    public function createAction(Request $request)
    {
        $fields = $request->request->all();
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，创建订单失败。');
        }

        if(!array_key_exists("targetId", $fields) || !array_key_exists("targetType", $fields)) {
            return $this->createMessageResponse('error', '订单中没有购买的内容，不能创建!');
        }
        
        $targetType = $fields["targetType"];
        $targetId = $fields["targetId"];

        $priceType = "RMB";
        $coinSetting = $this->getSettingService()->get("coin");
        $coinEnabled = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"];
        if($coinEnabled && isset($coinSetting["price_type"])) {
            $priceType = $coinSetting["price_type"];
        }
        $cashRate = 1;
        if($coinEnabled && isset($coinSetting["cash_rate"])) {
            $cashRate = $coinSetting["cash_rate"];
        }

        $processor = OrderProcessorFactory::create($targetType);

        try {
            
            list($amount, $totalPrice, $couponResult, $couponDiscount) = $processor->shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $fields);
            $amount = (float)$amount;
            $shouldPayMoney = (float)$fields["shouldPayMoney"];
            //价格比较
            if(abs($amount - $shouldPayMoney) > 0.01) {
                return $this->createMessageResponse('error', '支付价格不匹配，不能创建订单!');
            }

            $orderFileds = array(
                'priceType' => $priceType,
                'totalPrice' => $totalPrice,
                'amount' => $amount,
                'coinRate' => $cashRate,
                'coinAmount' => empty($fields["coinPayAmount"])?0:$fields["coinPayAmount"],
                'userId' => $user["id"],
                'payment' => 'alipay',
                'targetId' => $targetId,
                'coupon' => empty($couponResult) ? null : $fields["couponCode"],
                'couponDiscount' => empty($couponDiscount) ? null : $couponDiscount,
            );

            $order = $processor->createOrder($orderFileds, $fields);

            return $this->redirect($this->generateUrl('pay_center_show', array(
                'id' => $order['id']
            )));
        } catch (\Exception $e) {
            return $this->createMessageResponse('error', $e->getMessage());
        }

    }

    public function submitPayRequestAction(Request $request , $order, $requestParams)
    {
        $paymentRequest = $this->createPaymentRequest($order, $requestParams);
        
        return $this->render('TopxiaWebBundle:Order:submit-pay-request.html.twig', array(
            'form' => $paymentRequest->form(),
            'order' => $order,
        ));
    }

    public function resultNoticeAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:Order:resultNotice.html.twig');
    }

    public function couponCheckAction (Request $request, $type, $id)
    {
        if ($request->getMethod() == 'POST') {
            $code = $request->request->get('code');

            //判断coupon是否合法，是否存在跟是否过期跟是否可用于当前课程
            $course = $this->getCourseService()->getCourse($id);

            $couponInfo = $this->getCouponService()->checkCouponUseable($code, $type, $id, $course['price']);
            
            return $this->createJsonResponse($couponInfo);
        }
    }

    protected function doPayReturn(Request $request, $name, $successCallback = null)
    {
        $this->getLogService()->info('order', 'pay_result',  "{$name}页面跳转支付通知", $request->query->all());
        $response = $this->createPaymentResponse($name, $request->query->all());

        $payData = $response->getPayData();

        if ($payData['status'] == "waitBuyerConfirmGoods") {
            return $this->forward("TopxiaWebBundle:Order:resultNotice");
        }

        list($success, $order) = $this->getOrderService()->payOrder($payData);

        if ($order['status'] == 'paid' and $successCallback) {
            $successUrl = $successCallback($success, $order);
        }

        $goto = empty($successUrl) ? $this->generateUrl('homepage', array(), true) : $successUrl;
        return $this->redirect($goto);
    }

    protected function doPayNotify(Request $request, $name, $successCallback = null)
    {
        $this->getLogService()->info('order', 'pay_result', "{$name}服务器端支付通知", $request->request->all());
        $response = $this->createPaymentResponse($name, $request->request->all());

        $payData = $response->getPayData();
        try {
            list($success, $order) = $this->getOrderService()->payOrder($payData);
            if ($order['status'] == 'paid' and $successCallback) {
                $successCallback($success, $order);
            }

            return new Response('success');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function createPaymentRequest($order, $requestParams)
    {   
        $options = $this->getPaymentOptions($order['payment']);
        $request = Payment::createRequest($order['payment'], $options);

        $requestParams = array_merge($requestParams, array(
            'orderSn' => $order['sn'],
            'title' => $order['title'],
            'summary' => '',
            'amount' => $order['amount'],
        ));
        return $request->setParams($requestParams);
    }

    private function createPaymentResponse($name, $params)
    {
        $options = $this->getPaymentOptions($name);
        $response = Payment::createResponse($name, $options);

        return $response->setParams($params);
    }


    private function getPaymentOptions($payment)
    {
        $settings = $this->setting('payment');

        if (empty($settings)) {
            throw new \RuntimeException('支付参数尚未配置，请先配置。');
        }

        if (empty($settings['enabled'])) {
            throw new \RuntimeException("支付模块未开启，请先开启。");
        }

        if (empty($settings[$payment. '_enabled'])) {
            throw new \RuntimeException("支付模块({$payment})未开启，请先开启。");
        }

        if (empty($settings["{$payment}_key"]) or empty($settings["{$payment}_secret"])) {
            throw new \RuntimeException("支付模块({$payment})参数未设置，请先设置。");
        }

        $options = array(
            'key' => $settings["{$payment}_key"],
            'secret' => $settings["{$payment}_secret"],
            'type' => $settings["{$payment}_type"]
        );

        return $options;
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');   
    }

    protected function getCashService()
    {
        return $this->getServiceKernel()->createService('Cash.CashService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getCouponService()
    {
        return $this->getServiceKernel()->createService('Coupon:Coupon.CouponService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}