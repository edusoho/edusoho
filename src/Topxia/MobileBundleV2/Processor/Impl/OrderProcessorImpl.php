<?php
namespace Topxia\MobileBundleV2\Processor\Impl;

use Topxia\MobileBundleV2\Processor\BaseProcessor;
use Topxia\MobileBundleV2\Processor\OrderProcessor;
use Topxia\MobileBundleV2\Alipay\MobileAlipayConfig;

class OrderProcessorImpl extends BaseProcessor implements OrderProcessor
{
	public function payCourse()
	{
		$courseId = $this->getParam("courseId");
                        if (empty($courseId)) {
                            return $this->createErrorResponse('not_courseId', '没有找到加入的课程信息！');
                        }

		$token = $this->controller->getUserToken($this->request);
                        $user = $this->controller->getUser();
		if (!$user->isLogin()) {
            		return $this->createErrorResponse('not_login', '用户未登录，加入学习失败！');
        		}

        		$this->formData['courseId'] = $courseId;
        		$order = $this->controller->getCourseOrderService()->createOrder($this->formData);

        		if ($order['status'] == 'paid') {
            		return array('status' => 'ok', 'paid' => true, "message"=>"", "payUrl"=>"");
                        }

                        return $this->payCourseByAlipay($order, $token);
	}

            private function payCourseByAlipay($order, $token)
            {
                $result = array(
                    'status' => 'error',
                     'message' => '',
                     'paid' => false,
                     'payUrl' => ""
                );
                $payment = $this->controller->setting('payment', array());
                if (empty($payment['enabled'])) {
                    $result["message"] = "支付功能未开启！";
                    return $result;
                }

                if (empty($payment['alipay_enabled'])) {
                    $result["message"] = "支付功能未开启！";
                    return $result;
                }

                if (empty($payment['alipay_key']) or empty($payment['alipay_secret']) or empty($payment['alipay_account'])) {
                    $result["message"] = "支付宝参数不正确！";
                    return $result;
                }

                if (empty($payment['alipay_type']) or $payment['alipay_type'] != 'direct') {
                    $payUrl = $this->controller->generateUrl('mapi_order_submit_pay_request', array('id' => $order['id'], 'token' => $token), true);
                    $result["payUrl"] = $payUrl . '?token=' . $token;
                } else {
                    $result["payUrl"] = MobileAlipayConfig::createAlipayOrderUrl($this->request, "edusoho", $order);
                }

                $result["status"] = "ok";
                return $result;

            }
}