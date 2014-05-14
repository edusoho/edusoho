<?php

namespace Topxia\MobileBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\MobileBundle\Alipay\MobileAlipayRequest;
use Symfony\Component\HttpFoundation\Response;
use Topxia\MobileBundle\Alipay\AlipayNotify;
use Topxia\MobileBundle\Alipay\MobileAlipayConfig;

class MobileAlipayController extends MobileController
{
    public function showPayPageAction(Request $request)
    {
    	return $this->redirect("/Alipay/index.html");
    }

    public function payResultAction()
    {
        return new Response("success");
    }

    public function payAction(Request $request)
    {
    	$alipayRequest = new MobileAlipayRequest($request);
    	return new Response($alipayRequest->getRequestForm("edusoho"));
    }

    public function payNotifyAction(Request $request)
    {
        $result = array("status"=>"error");

        $alipayNotify = new AlipayNotify(MobileAlipayConfig::getAlipayConfig("edusoho"));
        $verify_result = $alipayNotify->verifyNotify();

        if($verify_result) {
            //验证成功
            $doc = simplexml_load_string($notify_data);
            $doc = (array)$doc;
            
            if( ! empty($doc['notify']) ) {
                //商户订单号
                $out_trade_no = $doc['out_trade_no'];
                //支付宝交易号
                $trade_no = $doc['trade_no'];
                //交易状态
                $trade_status = $doc['trade_status'];
                
                if($_POST['trade_status'] == 'TRADE_FINISHED') {
                    //判断该笔订单是否在商户网站中已经做过处理
                    $result["status"] = "success";
                }
                else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                    //判断该笔订单是否在商户网站中已经做过处理
                    $result["status"] = "success";
                }
            }

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）
        }
        else {
            //验证失败
            $result["status"] = "fail";
        }
        return $this->createJson($request, $result);
    }

    public function payCallBackAction(Request $request)
    {
        $status = "fail";
        $alipayNotify = new AlipayNotify(MobileAlipayConfig::getAlipayConfig("edusoho"));
        $verify_result = $alipayNotify->verifyReturn();
        if($verify_result) {
            //验证成功
            //商户订单号
            $out_trade_no = $_GET['out_trade_no'];

            //支付宝交易号
            $trade_no = $_GET['trade_no'];

            //交易状态
            $result = $_GET['result'];
                
            $status = "success";
        }
        else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            $status = "fail";
        }

        $callback = "<script type='text/javascript'>window.location='objc://alipayCallback?" . $status . "';</script>";
        return new Response($callback);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
