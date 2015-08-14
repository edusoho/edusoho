<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Component\OAuthClient\OAuthClientFactory;
use Topxia\Service\Util\CloudClientFactory;

class FinanceSettingController extends BaseController
{
    public function paymentAction(Request $request)
    {
        $payment = $this->getSettingService()->get('payment', array());
        $default = array(
            'enabled' => 0,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway' => 'none',
            'alipay_enabled' => 0,
            'alipay_key' => '',
            'alipay_secret' => '',
            'alipay_account' => '',
            'alipay_type' => 'direct',
            'tenpay_enabled' => 0,
            'tenpay_key' => '',
            'tenpay_secret' => '',
            'wxpay_enabled' => 0,
            'wxpay_key' => '',
            'wxpay_secret' => '',
            'wxpay_account' => '',
        );

        $payment = array_merge($default, $payment);
        if ($request->getMethod() == 'POST') {
            $payment = $request->request->all();
            $payment['alipay_key'] = trim($payment['alipay_key']);
            $payment['alipay_secret'] = trim($payment['alipay_secret']);
            $payment['wxpay_key'] = trim($payment['wxpay_key']);
            $payment['wxpay_secret'] = trim($payment['wxpay_secret']);

            $this->getSettingService()->set('payment', $payment);
            $this->getLogService()->info('system', 'update_settings', "更支付方式设置", $payment);
            $this->setFlashMessage('success', '支付方式设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:payment.html.twig', array(
            'payment' => $payment,
        ));
    }

    public function refundAction(Request $request)
    {
        $refundSetting = $this->getSettingService()->get('refund', array());
        $default = array(
            'maxRefundDays' => 0,
            'applyNotification' => '',
            'successNotification' => '',
            'failedNotification' => '',
        );

        $refundSetting = array_merge($default, $refundSetting);

        if ($request->getMethod() == 'POST') {
            $refundSetting = $request->request->all();
            $this->getSettingService()->set('refund', $refundSetting);
            $this->getLogService()->info('system', 'update_settings', "更新退款设置", $refundSetting);
            $this->setFlashMessage('success', '退款设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:refund.html.twig', array(
            'refundSetting' => $refundSetting,
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
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