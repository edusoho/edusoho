<?php

namespace Topxia\MobileBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\MobileBundle\Alipay\MobileAlipayRequest;
use Symfony\Component\HttpFoundation\Response;
use Topxia\MobileBundle\Alipay\AlipayNotify;
use Topxia\MobileBundle\Alipay\MobileAlipayConfig;
use Topxia\Component\Payment\Payment;
use Topxia\MobileBundle\Alipay\MobileAlipayResponse;

class MobileAlipayController extends MobileController
{

    public function payAction(Request $request)
    {
    	$alipayRequest = new MobileAlipayRequest($request);
    	return new Response($alipayRequest->getRequestForm("edusoho"));
    }

    public function payNotifyAction(Request $request, $name)
    {
        $this->getLogService()->info('notify', 'create', "paynotify action");
        $alipayNotify = new AlipayNotify(MobileAlipayConfig::getAlipayConfig("edusoho"));
        $verify_result = $alipayNotify->verifyNotify();

        if($verify_result) {
            //验证成功
            try {
                $status = $this->doPayNotify($request, $name);
            }catch(\Exception $e) {
                error_log($e->getMessage(), 0);
            }
        }
        else {
            //验证失败
            $result["status"] = "fail";
            $this->getLogService()->info('notify', 'check_fail', "paynotify action");
        }
        return new Response("success");
    }

    public function payCallBackAction(Request $request, $name)
    {
        $status = $this->doPayNotify($request, $name);
        $callback = "<script type='text/javascript'>window.location='objc://alipayCallback?" . $status . "';</script>";
        return new Response($callback);
    }

    //支付校验
    protected function doPayNotify(Request $request, $name)
    {
        if ($request->getMethod() == "GET") {
            $requestParams = $request->query->all();
            $order = $this->getOrderService()->getOrderBySn($requestParams['out_trade_no']);
            $requestParams['total_fee'] = $order['amount'];
        } else {
            $this->getLogService()->info('order', 'pay_result', "{$name}服务器端支付通知");
            $doc = simplexml_load_string($_POST['notify_data']);
            $doc = (array)$doc;
            
            $requestParams = array();
            if(!empty($doc['out_trade_no']) ) {
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

        $payData = $this->createPaymentResponse($requestParams);
        
        try {
            list($success, $order) = $this->getPayCenterService()->pay($payData);

            return "success";
        } catch (\Exception $e) {
            return "fail";
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

    public function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getPayCenterService()
    {
        return $this->getServiceKernel()->createService('PayCenter.PayCenterService');
    }

    protected function getCourseOrderService()
    {
        return $this->getServiceKernel()->createService('Course.CourseOrderService');
    }
}
