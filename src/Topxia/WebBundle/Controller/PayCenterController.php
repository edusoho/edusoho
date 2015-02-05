<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Component\Payment\Payment;
use Topxia\Service\Order\OrderProcessor\OrderProcessorFactory;

class PayCenterController extends BaseController
{
	public function showAction(Request $request)
	{

        $user = $this->getCurrentUser();

        if(!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，不能支付。');
        }

        $paymentSetting = $this->setting("payment");
        if(!isset($paymentSetting["enabled"]) || $paymentSetting["enabled"] == 0) {
            return $this->createMessageResponse('error', '支付中心未开启。');
        }

		$fields = $request->query->all();
		$order = $this->getOrderService()->getOrderBySn($fields["sn"]);

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

		return $this->render('TopxiaWebBundle:PayCenter:show.html.twig', array(
            'order' => $order,
            'payments' => $this->getEnabledPayments(),
        ));
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

        return $this->redirect($goto);
    }

    public function payErrorAction(Request $request)
    {
        return $this->createMessageResponse('error', '由于余额不足，支付失败，订单已被取消。');
    }

    public function payNotifyAction(Request $request, $name)
    {
        $this->getLogService()->info('order', 'pay_result', "{$name}服务器端支付通知", $request->request->all());
        $response = $this->createPaymentResponse($name, $request->request->all());

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

        return new Response('failture');
    }

    public function showTargetAction(Request $request)
    {
        $orderId = $request->query->get("id");
        $order = $this->getOrderService()->getOrder($orderId);

        $processor = OrderProcessorFactory::create($order["targetType"]);
        $router = $processor->getRouter();
        return $this->redirect($this->generateUrl($router, array('id' => $order['targetId'])));
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
        
        return $this->render('TopxiaWebBundle:PayCenter:submit-pay-request.html.twig', array(
            'form' => $paymentRequest->form(),
            'order' => $order,
        ));
    }

    public function resultNoticeAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:PayCenter:resultNotice.html.twig');
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

    private function createPaymentResponse($name, $params)
    {
        $options = $this->getPaymentOptions($name);
        $response = Payment::createResponse($name, $options);

        return $response->setParams($params);
    }

	private function getEnabledPayments()
    {
        $enableds = array();

        $setting = $this->setting('payment', array());

        if (empty($setting['enabled'])) {
            return $enableds;
        }

        $payNames = array('alipay');
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


}