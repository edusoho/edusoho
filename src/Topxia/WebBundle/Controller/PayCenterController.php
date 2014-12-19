<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Component\Payment\Payment;
use Topxia\Service\Order\OrderProcessor\OrderProcessorFactory;

class PayCenterController extends BaseController
{

	public function showAction(Request $request)
	{
		$fields = $request->query->all();
		$order = $this->getOrderService()->getOrder($fields["id"]);

		return $this->render('TopxiaWebBundle:PayCenter:show.html.twig', array(
            'order' => $order,
            'payments' => $this->getEnabledPayments(),
            'returnUrl' => $fields["returnUrl"],
            'notifyUrl' => $fields["notifyUrl"],
            'showUrl' => $fields["showUrl"],
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
            return $this->redirect($this->generateUrl('course_show', array('id' => $order['targetId'])));
        } else {

            $payRequestParams = array(
                'returnUrl' => $this->generateUrl('pay_return', array('name' => $order['payment']), true),
                'notifyUrl' => $this->generateUrl('pay_notify', array('name' => $order['payment']), true),
                'showUrl' => $this->generateUrl('pay_target_show', array('id' => $order['id']), true),
            );

            return $this->forward('TopxiaWebBundle:Order:submitPayRequest', array(
                'order' => $order,
                'requestParams' => $payRequestParams,
            ));
        }
	}

    public function payReturnAction(Request $request, $name)
    {
        $orderId = $request->query->get("id");
        $order = $this->getOrderService()->getOrder($orderId);

        $processor = OrderProcessorFactory::create($order["targetType"]);

        return $this->doPayReturn($request, $name, $processor->doPayReturn);
    }

    private function doPayReturn(Request $request, $name, $successCallback = null)
    {
        $this->getLogService()->info('order', 'pay_result',  "{$name}页面跳转支付通知", $request->query->all());
        $response = $this->createPaymentResponse($name, $request->query->all());

        $payData = $response->getPayData();

        if ($payData['status'] == "waitBuyerConfirmGoods") {
            return $this->forward("TopxiaWebBundle:Order:resultNotice");
        }

        $this->getPayCenterService()->pay($payData, $successCallback);

        list($success, $order) = $this->getOrderService()->payOrder($payData);

        if ($order['status'] == 'paid' and $successCallback) {
            $router = $successCallback($success, $order);
            $successUrl = $this->generateUrl($router, array('id' => $order["targetId"]), true);
        }

        $goto = empty($successUrl) ? $this->generateUrl('homepage', array(), true) : $successUrl;

        return $this->redirect($goto);
    }

    public function payNotifyAction(Request $request, $name)
    {
        $orderId = $request->query->get("id");
        $order = $this->getOrderService()->getOrder($orderId);

        $processor = OrderProcessorFactory::create($order["targetType"]);

        return $this->doPayNotify($request, $name, $processor->doPayNotify);
    }

    protected function doPayNotify(Request $request, $name, $successCallback = null)
    {
        $this->getLogService()->info('order', 'pay_result', "{$name}服务器端支付通知", $request->request->all());
        $response = $this->createPaymentResponse($name, $request->request->all());

        $payData = $response->getPayData();

        $this->getPayCenterService()->pay($payData);

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

    public function show1Action(Request $request)
    {
        $orderId = $request->query->get("id");
        $order = $this->getOrderService()->getOrder($orderId);

        $processor = OrderProcessorFactory::create($order["targetType"]);

        $router = $processor->getRouter();

        return $this->redirect($this->generateUrl($router, array('id' => $order['targetId'])));
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

	protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getPayCenterService()
    {
        return $this->getServiceKernel()->createService('PayCenter.PayCenterService');
    }


}