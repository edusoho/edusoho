<?php 

namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Service\Order\OrderProcessor\OrderProcessorFactory;
use Topxia\Component\Payment\Payment;

class PayCenterController extends BaseController
{
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

            return $this->forward('CustomWebBundle:Order:submitPayRequest', array(
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
            return $this->forward("TopxiaWebBundle:Order:resultNotice");
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

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
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

    protected function getPayCenterService()
    {
        return $this->getServiceKernel()->createService('PayCenter.PayCenterService');
    }
}