<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Component\Payment\Payment;
use Topxia\Service\Order\OrderProcessor\OrderProcessorFactory;
use Symfony\Component\DependencyInjection\SimpleXMLElement;



class PayCenterController extends BaseController
{
	public function showAction(Request $request)
	{

        $user = $this->getCurrentUser();

        if(!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，不能支付。');
        }

        $paymentSetting = $this->setting('payment');
        if(!isset($paymentSetting['enabled']) || $paymentSetting['enabled'] == 0) {
            if (!isset($paymentSetting['disabled_message'])) {
                $paymentSetting['disabled_message'] = '尚未开启支付模块，无法购买课程。';
            }
            return $this->createMessageResponse('error', $paymentSetting['disabled_message']);
        }

		$fields = $request->query->all();

		$order = $this->getOrderService()->getOrderBySn($fields["sn"]);
        $orderInfo = $this->getOrderInfo($order);
        if (empty($order)) {
            return $this->createMessageResponse('error', '订单不存在!');
        }

        if($order["userId"] != $user["id"]){
            return $this->createMessageResponse('error', '不是您的订单，不能支付');
        }

        if($order["status"] != "created") {
            return $this->createMessageResponse('error', '订单状态被更改，不能支付');
        }

        if(($order["createdTime"] + 40*60*60) < time()) {
            return $this->createMessageResponse('error', '订单已经过期，不能支付');
        }

        if($order["amount"] == 0 && $order["coinAmount"] == 0) {
            $payData = array(
                'sn' => $order['sn'],
                'status' => 'success', 
                'amount' => $order['amount'], 
                'paidTime' => time()
            );
            $this->getPayCenterService()->processOrder($payData);

            $processor = OrderProcessorFactory::create($order["targetType"]);
            $router = $processor->getRouter();

            return $this->redirect($this->generateUrl($router, array('id' => $order['targetId'])));
        } else if ($order["amount"] == 0 && $order["coinAmount"] > 0) {
            $payData = array(
                'sn' => $order['sn'],
                'status' => 'success', 
                'amount' => $order['amount'], 
                'paidTime' => time()
            );
            list($success, $order) = $this->getPayCenterService()->pay($payData);
            $processor = OrderProcessorFactory::create($order["targetType"]);
            $router = $processor->getRouter();

            $goto = $success && !empty($router) ? $this->generateUrl($router, array('id' => $order["targetId"]), true):$this->generateUrl('homepage', array(), true);

            return $this->redirect($goto);
        }
        $orderInfo['order'] = $order;
        $orderInfo['payments'] =  $this->getEnabledPayments();
		return $this->render('TopxiaWebBundle:PayCenter:show.html.twig', $orderInfo);
	}

	public function payAction(Request $request)
	{
		$fields = $request->request->all();
		$user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，支付失败。');
        }

		if(!array_key_exists("orderId", $fields)) {
			return $this->createMessageResponse('error', '缺少订单，支付失败');
		}

        if (!isset($fields['payment'])) {
            return $this->createMessageResponse('error', '支付方式未开启，请先开启');
        }

        $this->getOrderService()->updateOrder($fields["orderId"],array('payment' => $fields['payment']));
        $order = $this->getOrderService()->getOrder($fields["orderId"]);

		if($user["id"] != $order["userId"]) {
			return $this->createMessageResponse('error', '不是您创建的订单，支付失败');
		}

		if ($order['status'] == 'paid') {
            $processor = OrderProcessorFactory::create($order["targetType"]);
            $router = $processor->getRouter();

            return $this->redirect($this->generateUrl($router, array('id' => $order['targetId'])));
        } else {

            $payRequestParams = array(
                'returnUrl' => $this->generateUrl('pay_return', array('name' => $order['payment']), true),
                'notifyUrl' => $this->generateUrl('pay_notify', array('name' => $order['payment']), true),
                'showUrl' => $this->generateUrl('pay_success_show', array('id' => $order['id']), true),
            );

            return $this->forward('TopxiaWebBundle:PayCenter:submitPayRequest', array(
                'order' => $order,
                'requestParams' => $payRequestParams,
            ));
        }
	}

    public function payReturnAction(Request $request, $name, $successCallback = null)
    {
        $this->getLogService()->info('order', 'pay_result',  "{$name}页面跳转支付通知", $request->query->all());
        $response = $this->createPaymentResponse($name, $request->query->all());

        $payData = $response->getPayData();

        if ($payData['status'] == "waitBuyerConfirmGoods") {
            return $this->forward("TopxiaWebBundle:PayCenter:resultNotice");
        }

        list($success, $order) = $this->getPayCenterService()->pay($payData);

        if(!$success) {
            return $this->redirect("pay_error");
        }

        $processor = OrderProcessorFactory::create($order["targetType"]);
        $router = $processor->getRouter();

        $goto = !empty($router) ? $this->generateUrl($router, array('id' => $order["targetId"]), true) : $this->generateUrl('homepage', array(), true);

        return $this->render('TopxiaWebBundle:PayCenter:pay-return.html.twig',array(
            'goto'=> $goto,
        ));
    }

    public function payErrorAction(Request $request)
    {
        return $this->createMessageResponse('error', '由于余额不足，支付失败，订单已被取消。');
    }

    public function payNotifyAction(Request $request, $name)
    {
        $this->getLogService()->info('order', 'pay_result', "{$name}服务器端支付通知", $request->request->all());
        if ($name == 'alipay') {
            $response = $this->createPaymentResponse($name, $request->request->all());
        }
        elseif ($name == 'wxpay') {
            $returnXml = $request->getContent();
            $returnArray = $this->fromXml($returnXml);
            $response = $this->createPaymentResponse($name, $returnArray);
        }
        $payData = $response->getPayData();
        if ($payData['status'] == "waitBuyerConfirmGoods") {
            return new Response('success');
        }

        if ($payData['status'] == "success") {
            list($success, $order) = $this->getPayCenterService()->pay($payData);
            $processor = OrderProcessorFactory::create($order["targetType"]);

            if($success){
                return new Response('success');
            }
        }

        if($payData['status'] == "closed") {
            $order = $this->getOrderService()->getOrderBySn($payData['sn']);
            $this->getOrderService()->cancelOrder($order["id"], '{$name}交易订单已关闭', $payData);
            return new Response('success');
        }

        if($payData['status'] == "created") {
            $order = $this->getOrderService()->getOrderBySn($payData['sn']);
            $this->getOrderService()->createPayRecord($order["id"], $payData);
            return new Response('success');
        }

        return new Response('failture');
    }

    public function showTargetAction(Request $request)
    {
        $orderId = $request->query->get("id");
        $order = $this->getOrderService()->getOrder($orderId);

        $processor = OrderProcessorFactory::create($order["targetType"]);
        $router = $processor->getRouter();
        $router = $this->generateUrl($router, array('id' => $order['targetId']));

        return $this->render('TopxiaWebBundle:PayCenter:pay-return.html.twig',array(
            'goto'=> $router,
            ));
    }

    public function payPasswordCheckAction(Request $request)
    {
        $password = $request->query->get('value');

        $user = $this->getCurrentUser();

        if(!$user->isLogin()) {
            $response = array('success' => false, 'message' => '用户未登录');
        }
        
        $isRight = $this->getAuthService()->checkPayPassword($user["id"], $password);
        if(!$isRight) {
            $response = array('success' => false, 'message' => '支付密码不正确');
        } else {
            $response = array('success' => true, 'message' => '支付密码正确');
        }

        return $this->createJsonResponse($response);
    }

    public function submitPayRequestAction(Request $request , $order, $requestParams)
    {
        $paymentRequest = $this->createPaymentRequest($order, $requestParams);
        $formRequest = $paymentRequest->form();
        $params = $formRequest['params'];
        $payment = $request->request->get('payment');
        if ($payment == 'alipay') {
            return $this->render('TopxiaWebBundle:PayCenter:submit-pay-request.html.twig', array(
                'form' => $paymentRequest->form(),
                'order' => $order,
            ));
        }
        elseif ($payment == 'wxpay') {
            $returnXml = $paymentRequest->unifiedOrder();
            if(!$returnXml){
                throw new \RuntimeException("xml数据异常！");
            }
            $returnArray = $paymentRequest->fromXml($returnXml);
            if($returnArray['return_code'] == 'SUCCESS'){
                $url = $returnArray['code_url'];
                return $this->render('TopxiaWebBundle:PayCenter:wxpay-qrcode.html.twig', array(
                    'url' => $url,
                    'order' => $order,
                ));
            }
            else{
                throw new \RuntimeException($returnArray['return_msg']);
            }
        }
    }

    public function wxpayRollAction(Request $request)
    {
        $order = $request->query->get('order');
        if ($order['status'] == 'paid') {
            return $this->createJsonResponse(true);
        }
        else{
            $paymentRequest = $this->createPaymentRequest($order, array(
            'returnUrl' => '',
            'notifyUrl' => '',
            'showUrl' => '',
            ));
            $returnXml = $paymentRequest->orderQuery();
            $returnArray = $this->fromXml($returnXml);
            if ($returnArray['trade_state'] == 'SUCCESS') {
                $payData =array();
                $payData['status'] = 'success';
                $payData['payment'] = 'wxpay';
                $payData['amount'] = $order['amount'];
                $payData['paidTime'] = time();
                $payData['sn'] = $returnArray['out_trade_no'];
                if (isset($order['targetType'])) {
                    list($success, $order) = $this->getPayCenterService()->pay($payData);
                }
                else {
                    list($success, $order) = $this->getCashOrdersService()->payOrder($payData);

                }

                if ($success){
                    return $this->createJsonResponse(true);
                }
            }
        }
        return $this->createJsonResponse(false);
    }

    public function orderQueryAction(Request $request)
    {
        $orderId = $request->query->get('orderId');
        $order = $this->getOrderService()->getOrder($orderId);
        $paymentRequest = $this->createPaymentRequest($order, array(
            'returnUrl' => '',
            'notifyUrl' => '',
            'showUrl' => '',
        ));
        $returnXml = $paymentRequest->orderQuery();
        $returnArray = $this->fromXml($returnXml);
        if ($returnArray['trade_state'] == 'SUCCESS') {
            $payData =array();
            $payData['status'] = 'success';
            $payData['payment'] = 'wxpay';
            $payData['amount'] = $order['amount'];
            $payData['paidTime'] = time();
            $payData['sn'] = $returnArray['out_trade_no'];
            list($success, $order) = $this->getPayCenterService()->pay($payData);
            $processor = OrderProcessorFactory::create($order["targetType"]);
            
            if($success){
                return $this->createJsonResponse(true);
            }
            else{
                return $this->createJsonResponse(false);
            }
        }
        else{
            return $this->createJsonResponse(false);
        }

    }

    public function resultNoticeAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:PayCenter:resultNotice.html.twig');
    }

    protected function createPaymentRequest($order, $requestParams)
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


    protected function getOrderInfo($order)
    {
        $fields = array('targetType' => $order['targetType'], 'targetId' => $order['targetId']);
        if ($order['targetType'] ==  'vip') {
            $defaultBuyMonth = $this->setting('vip.default_buy_months');
            $fields['unit'] = $order['data']['unitType'];
            $fields['duration'] = $order['data']['duration'];
            $fields['defaultBuyMonth'] = $defaultBuyMonth;
            $fields['buyType'] = $order['data']['buyType'];   
        }
        $processor = OrderProcessorFactory::create($order['targetType']);
        $orderInfo = $processor->getOrderInfo($order['targetId'], $fields);

        return $orderInfo;
    }

    protected function getPaymentOptions($payment)
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

        if (empty($settings["{$payment}_key"]) || empty($settings["{$payment}_secret"])) {
            throw new \RuntimeException("支付模块({$payment})参数未设置，请先设置。");
        }
        if ($payment == 'alipay') {
            $options = array(
                'key' => $settings["{$payment}_key"],
                'secret' => $settings["{$payment}_secret"],
                'type' => $settings["{$payment}_type"]
            );
        }
        elseif ($payment == 'wxpay') {
            $options = array(
                'key' => $settings["{$payment}_key"],
                'secret' => $settings["{$payment}_secret"]
            );
        }

        return $options;
    }

    protected function createPaymentResponse($name, $params)
    {
        $options = $this->getPaymentOptions($name);
        $response = Payment::createResponse($name, $options);

        return $response->setParams($params);
    }

    private function fromXml($xml)
    {
        $array = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
        return $array;
    }

	protected function getEnabledPayments()
    {
        $enableds = array();

        $setting = $this->setting('payment', array());

        if (empty($setting['enabled'])) {
            return $enableds;
        }

        $payNames = array('alipay','wxpay');
        foreach ($payNames as $payName) {
            if (!empty($setting[$payName . '_enabled'])) {
                $enableds[$payName] = array(
                    'type' => empty($setting[$payName . '_type']) ? '' : $setting[$payName . '_type'],
                );
            }
        }

        return $enableds;
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

	protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getPayCenterService()
    {
        return $this->getServiceKernel()->createService('PayCenter.PayCenterService');
    }

    protected function getCashOrdersService(){
      
        return $this->getServiceKernel()->createService('Cash.CashOrdersService');
    }
}