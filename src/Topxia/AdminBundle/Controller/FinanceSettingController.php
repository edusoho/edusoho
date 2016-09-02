<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Topxia\Common\ArrayToolkit;

class FinanceSettingController extends BaseController
{
    public function paymentAction(Request $request)
    {
        $payment = $this->getSettingService()->get('payment', array());
        $default = array(
            'enabled'            => 0,
            'disabled_message'   => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'       => 'none',
            'alipay_enabled'     => 0,
            'alipay_key'         => '',
            'alipay_secret'      => '',
            'alipay_account'     => '',
            'alipay_type'        => 'direct',
            'tenpay_enabled'     => 0,
            'tenpay_key'         => '',
            'tenpay_secret'      => '',
            'wxpay_enabled'      => 0,
            'wxpay_key'          => '',
            'wxpay_secret'       => '',
            'wxpay_account'      => '',
            'heepay_enabled'     => 0,
            'heepay_key'         => '',
            'heepay_secret'      => '',
            'quickpay_enabled'   => 0,
            'quickpay_key'       => '',
            'quickpay_secret'    => '',
            'quickpay_aes'       => '',
            'llpay_enabled'    => 0,
            'llpay_key'        => '',
            'llpay_secret'     => ''
        );

        $payment = array_merge($default, $payment);
        
        if ($request->getMethod() == 'POST') {
            $payment                    = $request->request->all();
            $payment = ArrayToolkit::trim($payment);
            //新增支付方式，加入下列列表计算，以便判断是否关闭支付功能
            $payment = $this->isClosePayment($payment);
            $this->getSettingService()->set('payment', $payment);
            $this->getLogService()->info('system', 'update_settings', "更支付方式设置", $payment);
            $this->setFlashMessage('success', '支付方式设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:payment.html.twig', array(
            'payment' => $payment
        ));
    }

    public function isClosePayment($payment)
    {

        $payments = ArrayToolkit::parts($payment, array('alipay_enabled', 'wxpay_enabled', 'heepay_enabled', 'quickpay_enabled', 'llpay_enabled'));
        $sum = 0 ;
        foreach ($payments as $value) {
            $sum += $value;
        }

        if ($sum < 1) {
            $payment['enabled'] = 0;
        }

        return $payment;
    }

    public function refundAction(Request $request)
    {
        $refundSetting = $this->getSettingService()->get('refund', array());
        $default       = array(
            'maxRefundDays'       => 0,
            'applyNotification'   => '',
            'successNotification' => '',
            'failedNotification'  => ''
        );

        $refundSetting = array_merge($default, $refundSetting);

        if ($request->getMethod() == 'POST') {
            $refundSetting = $request->request->all();
            $this->getSettingService()->set('refund', $refundSetting);
            $this->getLogService()->info('system', 'update_settings', "更新退款设置", $refundSetting);
            $this->setFlashMessage('success', '退款设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:refund.html.twig', array(
            'refundSetting' => $refundSetting
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }
}
