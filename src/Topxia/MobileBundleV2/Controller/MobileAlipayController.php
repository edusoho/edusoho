<?php

namespace Topxia\MobileBundleV2\Controller;

use AppBundle\Component\Payment\Payment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\MobileBundleV2\Alipay\AlipayNotify;
use Topxia\MobileBundleV2\Alipay\MobileAlipayConfig;
use Topxia\MobileBundleV2\Alipay\MobileAlipayRequest;

class MobileAlipayController extends MobileBaseController
{
    public function payAction(Request $request)
    {
        $alipayRequest = new MobileAlipayRequest($request);

        return new Response($alipayRequest->getRequestForm('edusoho'));
    }

    public function payNotifyAction(Request $request, $name)
    {
        $this->getLogService()->info('notify', 'create', 'paynotify action');
        $alipayNotify = new AlipayNotify(MobileAlipayConfig::getAlipayConfig('edusoho'));
        $verify_result = $alipayNotify->verifyNotify();

        $status = 'fail';

        if ($verify_result) {
            //验证成功
            try {
                $status = $this->doPayNotify($request, $name);
            } catch (\Exception $e) {
                error_log($e->getMessage(), 0);
            }
        } else {
            //验证失败
            $status = 'fail';
            $this->getLogService()->info('notify', 'check_fail', 'paynotify action');
        }

        return new Response($status);
    }

    public function payMerchantAction(Request $request)
    {
        return new Response('<p>请点击返回按钮关闭!</p>');
    }

    public function payCallBackAction(Request $request, $name)
    {
        $status = 'success';
        $callback = "<script type='text/javascript'>window.location='objc://alipayCallback?".$status."';</script>";

        return new Response($callback);
    }

    //支付校验
    protected function doPayNotify(Request $request, $name)
    {
        // $response = $this->forward('TopxiaWebBundle:PayCenter:payNotify', array(
        //     'request' => $request,
        //     'name' => $name
        // ));

        // $this->getLogger('Mobile2DoPayNotify')->info('response code '.$response->getStatusCode());

        // if($response->getContent() == 'success') {
        //     return 'success';
        // }

        // return "fail";

        $requestParams = array();

        if ($request->getMethod() == 'GET') {
            $requestParams = $request->query->all();
            $order = $this->getOrderService()->getOrderBySn($requestParams['out_trade_no']);
            $requestParams['total_fee'] = $order['amount'];
        } else {
            $doc = simplexml_load_string($_POST['notify_data']);
            $doc = (array) $doc;
            $requestParams = array();

            if (!empty($doc['out_trade_no'])) {
                //商户订单号
                $requestParams['out_trade_no'] = $doc['out_trade_no'];
                //支付宝交易号
                $requestParams['trade_no'] = $doc['trade_no'];
                //交易状态
                $requestParams['trade_status'] = $doc['trade_status'];
                $requestParams['total_fee'] = $doc['total_fee'];
                $requestParams['gmt_payment'] = $doc['gmt_payment'];
            }
        }

        $this->getLogService()->info('order', 'pay_result', "{$name}服务器端支付通知", $requestParams);
        $payData = $this->createPaymentResponse($requestParams);

        try {
            list($success, $order) = $this->getPayCenterService()->pay($payData);

            return 'success';
        } catch (\Exception $e) {
            return 'fail';
        }
    }

    private function createPaymentResponse($params)
    {
        $data = array();
        $data['payment'] = 'alipay';
        $data['sn'] = $params['out_trade_no'];

        if (!empty($params['trade_status'])) {
            $data['status'] = in_array($params['trade_status'], array('TRADE_SUCCESS', 'TRADE_FINISHED')) ? 'success' : 'unknown';
        } elseif (!empty($params['result'])) {
            $data['status'] = $params['result'];
        }

        $data['amount'] = $params['total_fee'];

        if (!empty($params['gmt_payment'])) {
            $data['paidTime'] = strtotime($params['gmt_payment']);
        } elseif (!empty($params['notify_time'])) {
            $data['paidTime'] = strtotime($params['notify_time']);
        } else {
            $data['paidTime'] = time();
        }

        $data['raw'] = $params;

        return $data;
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

    private function getPaymentOptions($payment)
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

        $options = array(
            'key' => $settings["{$payment}_key"],
            'secret' => $settings["{$payment}_secret"],
            'type' => $settings["{$payment}_type"],
        );

        return $options;
    }

    protected function getPayCenterService()
    {
        return $this->createService('PayCenter:PayCenterService');
    }

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}
