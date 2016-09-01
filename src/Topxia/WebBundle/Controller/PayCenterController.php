<?php

namespace Topxia\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Topxia\Component\Payment\Payment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Order\OrderProcessor\OrderProcessorFactory;

class PayCenterController extends BaseController
{
    public function showAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，不能支付。');
        }

        $paymentSetting = $this->setting('payment');

        if (!isset($paymentSetting['enabled']) || $paymentSetting['enabled'] == 0) {
            if (!isset($paymentSetting['disabled_message'])) {
                $paymentSetting['disabled_message'] = '尚未开启支付模块，无法购买课程。';
            }

            return $this->createMessageResponse('error', $paymentSetting['disabled_message']);
        }

        $fields                  = $request->query->all();
        $orderInfo['sn']         = $fields['sn'];
        $orderInfo['targetType'] = $fields['targetType'];
        $orderInfo['isMobile']   = $this->isMobileClient();
        $processor               = OrderProcessorFactory::create($fields['targetType']);
        $orderInfo['template']   = $processor->getOrderInfoTemplate();
        $order                   = $processor->getOrderBySn($orderInfo['sn']);
        $targetId                = isset($order['targetId']) ? $order['targetId'] : '';
        $isTargetExist           = $processor->isTargetExist($targetId);

        if (!$isTargetExist) {
            return $this->createMessageResponse('error', '该订单已失效');
        }

        if (empty($order)) {
            return $this->createMessageResponse('error', '订单不存在!');
        }

        if ($order['userId'] != $user['id']) {
            return $this->createMessageResponse('error', '不是您的订单，不能支付');
        }

        if ($order['status'] != 'created') {
            return $this->createMessageResponse('error', '订单状态被更改，不能支付');
        }

        if (($order['createdTime'] + 40 * 60 * 60) < time()) {
            return $this->createMessageResponse('error', '订单已经过期，不能支付');
        }

        if (!empty($order['coupon'])) {
            $result = $this->getCouponService()->checkCouponUseable($order['coupon'], $order['targetType'], $order['targetId'], $order['amount']);

            if ($result['useable'] == 'no') {
                return $this->createMessageResponse('error', $result['message']);
            }
        }

        if ($order['amount'] == 0 && $order['coinAmount'] == 0) {
            $payData = array(
                'sn'       => $order['sn'],
                'status'   => 'success',
                'amount'   => $order['amount'],
                'paidTime' => time()
            );
            $this->getPayCenterService()->processOrder($payData);

            return $this->redirectOrderTarget($order);
        } elseif ($order['amount'] == 0 && $order['coinAmount'] > 0) {
            $payData = array(
                'sn'       => $order['sn'],
                'status'   => 'success',
                'amount'   => $order['amount'],
                'paidTime' => time(),
                'payment'  => 'coin'
            );
            list($success, $order) = $this->getPayCenterService()->pay($payData);

            if ($success) {
                return $this->redirectOrderTarget($order);
            } else {
                return $this->redirect($this->generateUrl('homepage', array(), true));
            }
        }

        $orderInfo['order']         = $order;
        $orderInfo['payments']      = $this->getEnabledPayments();
        $orderInfo['payAgreements'] = $this->getUserService()->findUserPayAgreementsByUserId($user['id']);

        foreach ($orderInfo['payments'] as $payment) {
            if ($payment['enabled']) {
                $orderInfo['firstEnabledPayment'] = $payment;
                break;
            }
        }

        return $this->render('TopxiaWebBundle:PayCenter:show.html.twig', $orderInfo);
    }

    public function redirectOrderTarget($order)
    {
        $processor = OrderProcessorFactory::create($order['targetType']);
        $goto      = $processor->callbackUrl($order, $this->container);

        return $this->render('TopxiaWebBundle:PayCenter:pay-return.html.twig', array(
            'goto' => $goto
        ));
    }

    public function payAction(Request $request)
    {
        $fields = $request->request->all();
        $user   = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，支付失败。');
        }

        if (!array_key_exists('orderId', $fields)) {
            return $this->createMessageResponse('error', '缺少订单，支付失败');
        }

        if (!isset($fields['payment'])) {
            return $this->createMessageResponse('error', '支付方式未开启，请先开启');
        }

        $order = OrderProcessorFactory::create($fields['targetType'])->updateOrder($fields['orderId'], array('payment' => $fields['payment']));

        if ($user['id'] != $order['userId']) {
            return $this->createMessageResponse('error', '不是您创建的订单，支付失败');
        }

        if ($order['status'] == 'paid') {
            return $this->redirectOrderTarget($order);
        } else {
            return $this->forward('TopxiaWebBundle:PayCenter:submitPayRequest', array(
                'order' => $order
            ));
        }
    }

    public function submitPayRequestAction(Request $request, $order)
    {
        if (empty($order['payment'])) {
            return $this->createMessageResponse('error', '请选择支付方式');
        }

        $requestParams = array(
            'returnUrl' => $this->generateUrl('pay_return', array('name' => $order['payment']), true),
            'notifyUrl' => $this->generateUrl('pay_notify', array('name' => $order['payment']), true),
            'showUrl'   => $this->generateUrl('pay_success_show', array('id' => $order['id']), true),
            'backUrl'   => $this->generateUrl('pay_center_show', array('sn' => $order['sn'], 'targetType' => $order['targetType']), true)
        );
        $payment = $request->request->get('payment');

        if ($payment == 'quickpay') {
            $authBank = array();

            $payAgreementId = $request->request->get('payAgreementId', '');

            if (!empty($payAgreementId)) {
                $authBank = $this->getUserService()->getUserPayAgreement($payAgreementId);
            }

            $requestParams['authBank'] = $authBank;
        }

        $requestParams['userAgent'] = $request->headers->get('User-Agent');
        $requestParams['isMobile']  = $this->isMobileClient();

        $paymentRequest = $this->createPaymentRequest($order, $requestParams);
        $formRequest    = $paymentRequest->form();
        $params         = $formRequest['params'];

        if ($payment == 'wxpay') {
            $returnXml = $paymentRequest->unifiedOrder();

            if (!$returnXml) {
                throw new \RuntimeException('xml数据异常！');
            }

            $returnArray = $paymentRequest->fromXml($returnXml);

            if ($returnArray['return_code'] == 'SUCCESS') {
                $url = $returnArray['code_url'];

                return $this->render('TopxiaWebBundle:PayCenter:wxpay-qrcode.html.twig', array(
                    'url'   => $url,
                    'order' => $order
                ));
            } else {
                throw new \RuntimeException($returnArray['return_msg']);
            }
        } elseif ($payment == 'heepay' || $payment == 'quickpay') {
            $order = $this->generateOrderToken($order, $params);
        }

        return $this->render('TopxiaWebBundle:PayCenter:submit-pay-request.html.twig', array(
            'form'  => $formRequest,
            'order' => $order
        ));
    }

    public function unbindAuthAction(Request $request)
    {
        $this->getLogService()->info('order', 'unbind-back', '银行卡解绑');
        $fields   = $request->request->all();
        $response = $this->verification($fields);

        if ($response) {
            return $this->createJsonResponse($response);
        }

        $authBank              = $this->getUserService()->getUserPayAgreement($fields['payAgreementId']);
        $requestParams         = array('authBank' => $authBank, 'payment' => 'quickpay', 'mobile' => $fields['mobile']);
        $unbindAuthBankRequest = $this->createUnbindAuthBankRequest($requestParams);
        $formRequest           = $unbindAuthBankRequest->form();

        return $this->createJsonResponse($formRequest);
    }

    public function showMobileAction(Request $request)
    {
        $fields   = $request->request->all();
        $response = $this->verification($fields);

        if ($response) {
            return $this->createJsonResponse($response);
        }

        return $this->render('TopxiaWebBundle:PayCenter:show-mobile.html.twig', array(
            'payAgreementId' => $fields['payAgreementId']
        ));
    }

    public function payReturnAction(Request $request, $name, $successCallback = null)
    {
        if ($name == 'llpay') {
             $returnArray              = $request->request->all();
            $returnArray['isMobile']  = $this->isMobileClient();
        } else {
            $returnArray = $request->query->all();
        }

        $this->getLogService()->info('order', 'pay_result', "{$name}页面跳转支付通知", $returnArray);
        $response = $this->createPaymentResponse($name, $returnArray);
        $payData  = $response->getPayData();

        if ($payData['status'] == 'waitBuyerConfirmGoods') {
            return $this->forward('TopxiaWebBundle:PayCenter:resultNotice');
        }

        if ($payData['status'] == 'insufficient balance') {
            return $this->createMessageResponse('error', '由于余额不足，支付失败，请重新支付。', null, 3000, $this->generateUrl('homepage'));
        }

        if (stripos($payData['sn'], 'o') !== false) {
            $order = $this->getCashOrdersService()->getOrderBySn($payData['sn']);
        } else {
            $order = $this->getOrderService()->getOrderBySn($payData['sn']);
        }
        list($success, $order) = OrderProcessorFactory::create($order['targetType'])->pay($payData);

        if (!$success) {
            return $this->redirect($this->generateUrl("pay_error"));
        }

        $processor = OrderProcessorFactory::create($order['targetType']);

        $goto = $processor->callbackUrl($order, $this->container);

        return $this->render('TopxiaWebBundle:PayCenter:pay-return.html.twig', array(
            'goto' => $goto
        ));
    }

    public function payErrorAction(Request $request)
    {
        return $this->createMessageResponse('error', '由于余额不足，支付失败，订单已被取消。');
    }

    public function payNotifyAction(Request $request, $name)
    {
        if ($name == 'wxpay') {
            $returnXml   = $request->getContent();
            $returnArray = $this->fromXml($returnXml);
        } elseif ($name == 'heepay' || $name == 'quickpay') {
            $returnArray = $request->query->all();
        } elseif ($name == 'llpay') {
            $returnArray              = json_decode(file_get_contents('php://input'), true);
            $returnArray['isMobile']  = $this->isMobileClient();
        } else {
            $returnArray = $request->request->all();
        }

        $this->getLogService()->info('order', 'pay_result', "{$name}服务器端支付通知", $returnArray);

        $response = $this->createPaymentResponse($name, $returnArray);

        $payData = $response->getPayData();
        if ($payData['status'] == 'waitBuyerConfirmGoods') {
            return new Response('success');
        }

        if (stripos($payData['sn'], 'o') !== false) {
            $order = $this->getCashOrdersService()->getOrderBySn($payData['sn']);
        } else {
            $order = $this->getOrderService()->getOrderBySn($payData['sn']);
        }

        $processor = OrderProcessorFactory::create($order['targetType']);

        if ($payData['status'] == 'success') {
            list($success, $order) = $processor->pay($payData);
            
            if ($success) {
                if ($name == 'llpay') {
                    return new Response("{'ret_code':'0000','ret_msg':'交易成功'}");
                } else {
                    return new Response('success');
                }
            }
        }

        if ($payData['status'] == 'closed') {
            $order = $processor->getOrderBySn($payData['sn']);
            $processor->cancelOrder($order['id'], '{$name}交易订单已关闭', $payData);

            return new Response('success');
        }

        if ($payData['status'] == 'created') {
            $order = $processor->getOrderBySn($payData['sn']);
            $processor->createPayRecord($order['id'], $payData);

            return new Response('success');
        }

        return new Response('failture');
    }

    public function showTargetAction(Request $request)
    {
        $orderId = $request->query->get('id');
        $order   = $this->getOrderService()->getOrder($orderId);

        $processor = OrderProcessorFactory::create($order['targetType']);
        $router    = $processor->callbackUrl($order, $this->container);

        return $this->render('TopxiaWebBundle:PayCenter:pay-return.html.twig', array(
            'goto' => $router
        ));
    }

    public function payPasswordCheckAction(Request $request)
    {
        $password = $request->query->get('value');

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $response = array('success' => false, 'message' => '用户未登录');
        }

        $isRight = $this->getAuthService()->checkPayPassword($user['id'], $password);

        if (!$isRight) {
            $response = array('success' => false, 'message' => '支付密码不正确');
        } else {
            $response = array('success' => true, 'message' => '支付密码正确');
        }

        return $this->createJsonResponse($response);
    }

    public function wxpayRollAction(Request $request)
    {
        $order = $request->query->get('order');

        if ($order['status'] == 'paid') {
            return $this->createJsonResponse(true);
        } else {
            $paymentRequest = $this->createPaymentRequest($order, array(
                'returnUrl' => '',
                'notifyUrl' => '',
                'showUrl'   => ''
            ));
            $returnXml   = $paymentRequest->orderQuery();
            $returnArray = $this->fromXml($returnXml);

            if ($returnArray['trade_state'] == 'SUCCESS') {
                $payData             = array();
                $payData['status']   = 'success';
                $payData['payment']  = 'wxpay';
                $payData['amount']   = $order['amount'];
                $payData['paidTime'] = time();
                $payData['sn']       = $returnArray['out_trade_no'];

                list($success, $order) = OrderProcessorFactory::create($order['targetType'])->pay($payData);

                if ($success) {
                    return $this->createJsonResponse(true);
                }
            }
        }

        return $this->createJsonResponse(false);
    }

    public function resultNoticeAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:PayCenter:resultNotice.html.twig');
    }

    protected function createPaymentRequest($order, $requestParams)
    {
        $options       = $this->getPaymentOptions($order['payment']);
        $request       = Payment::createRequest($order['payment'], $options);
        $processor     = OrderProcessorFactory::create($order['targetType']);
        $targetId      = isset($order['targetId']) ? $order['targetId'] : $order['id'];
        $requestParams = array_merge($requestParams, array(
            'orderSn'     => $order['sn'],
            'userId'      => $order['userId'],
            'title'       => $order['title'],
            'targetTitle' => $processor->getTitle($targetId),
            'summary'     => '',
            'note'        => $processor->getNote($targetId),
            'amount'      => $order['amount'],
            'targetType'  => $order['targetType']
        ));

        return $request->setParams($requestParams);
    }

    protected function createUnbindAuthBankRequest($params)
    {
        $options = $this->getPaymentOptions($params['payment']);
        $request = Payment::createUnbindAuthRequest($params['payment'], $options);

        return $request->setParams(array('authBank' => $params['authBank'], 'mobile' => $params['mobile']));
    }

    public function generateOrderToken($order, $params)
    {
        $processor = OrderProcessorFactory::create($order['targetType']);

        return $processor->updateOrder($order['id'], array('token' => $params['agent_bill_id']));
    }

    public function verification($fields)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return array('success' => false, 'message' => '用户未登录');
        }

        $authBanks = $this->getUserService()->findUserPayAgreementsByUserId($user['id']);
        $authBanks = ArrayToolkit::column($authBanks, 'id');

        if (!in_array($fields['payAgreementId'], $authBanks)) {
            return array('success' => false, 'message' => '不是您绑定的银行卡');
        }
    }

    protected function getPaymentOptions($payment)
    {
        $settings = $this->setting('payment');

        if (empty($settings)) {
            throw new \RuntimeException('支付参数尚未配置，请先配置。');
        }

        if (empty($settings['enabled'])) {
            throw new \RuntimeException('支付模块未开启，请先开启。');
        }

        if (empty($settings[$payment.'_enabled'])) {
            throw new \RuntimeException("支付模块({$payment})未开启，请先开启。");
        }

        if (empty($settings["{$payment}_key"]) || empty($settings["{$payment}_secret"])) {
            throw new \RuntimeException("支付模块({$payment})参数未设置，请先设置。");
        }

        if ($payment == 'alipay') {
            $options = array(
                'key'    => $settings["{$payment}_key"],
                'secret' => $settings["{$payment}_secret"],
                'type'   => $settings["{$payment}_type"]
            );
        } elseif ($payment == 'quickpay') {
            $options = array(
                'key'    => $settings["{$payment}_key"],
                'secret' => $settings["{$payment}_secret"],
                'aes'    => $settings["{$payment}_aes"]
            );
        } else {
            $options = array(
                'key'    => $settings["{$payment}_key"],
                'secret' => $settings["{$payment}_secret"]
            );
        }

        return $options;
    }

    protected function createPaymentResponse($name, $params)
    {
        $options  = $this->getPaymentOptions($name);
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

        $payment  = $this->get('topxia.twig.web_extension')->getDict('payment');
        $payNames = array_keys($payment);
        foreach ($payNames as $key => $payName) {
            if (!empty($setting[$payName.'_enabled'])) {
                $enableds[$key] = array(
                    'name'    => $payName,
                    'enabled' => $setting[$payName.'_enabled']
                );

                if ($this->isWxClient() && $payName == 'alipay') {
                    $enableds[$key]['enabled'] = 0;
                }

                if ($this->isMobileClient() && $payName == 'heepay') {
                    $enableds[$key]['enabled'] = 0;
                }

                if ($this->isMobileClient() && $payName == 'llcbpay') {
                    $enableds[$key]['enabled'] = 0;
                }
            }
        }
        return $enableds;
    }

    protected function getCouponService()
    {
        return $this->getServiceKernel()->createService('Coupon.CouponService');
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

    protected function getCashOrdersService()
    {
        return $this->getServiceKernel()->createService('Cash.CashOrdersService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
