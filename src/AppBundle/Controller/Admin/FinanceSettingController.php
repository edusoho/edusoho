<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class FinanceSettingController extends BaseController
{
    public function paymentAction(Request $request)
    {
        $payment = $this->getSettingService()->get('payment', array());
        $default = array(
            'enabled' => 0,
            'disabled_message' => '由于网校未开通任一支付功能，当前商品不支持购买，请联系网校开通支付功能后再进行购买。',
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
            'wxpay_appid' => '',
            'wxpay_account' => '',
            'wxpay_key' => '',
            'wxpay_secret' => '',
            // 'heepay_enabled' => 0,
            // 'heepay_key' => '',
            // 'heepay_secret' => '',
            // 'quickpay_enabled' => 0,
            // 'quickpay_key' => '',
            // 'quickpay_secret' => '',
            // 'quickpay_aes' => '',
            'llpay_enabled' => 0,
            'llpay_key' => '',
            'llpay_accessKey' => '',
            'llpay_secretKey' => '',
        );
        $default['wxpay_mp_secret'] = $this->getWeixinMpFile();

        $payment = array_merge($default, $payment);
        if ('POST' == $request->getMethod()) {
            $payment = $request->request->all();
            $payment = ArrayToolkit::trim($payment);
            if (!$payment['enabled']) {
                $payment['alipay_enabled'] = 0;
                $payment['wxpay_enabled'] = 0;
                // $payment['heepay_enabled'] = 0;
                // $payment['quickpay_enabled'] = 0;
                $payment['llpay_enabled'] = 0;
            }
            $payment['disabled_message'] = empty($payment['disabled_message']) ? $default['disabled_message'] : $payment['disabled_message'];

            $formerPayment = $this->getSettingService()->get('payment');

            $payment = array_merge($formerPayment, $payment);

            $this->getSettingService()->set('payment', $payment);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin/system/payment.html.twig', array(
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

        if ('POST' == $request->getMethod()) {
            $refundSetting = $request->request->all();
            $this->getSettingService()->set('refund', $refundSetting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin/system/refund.html.twig', array(
            'refundSetting' => $refundSetting,
        ));
    }

    private function getWeixinMpFile()
    {
        $dir = $this->container->getParameter('kernel.root_dir').'/../web';
        $mp_secret = array_map('file_get_contents', glob($dir.'/MP_verify_*.txt'));

        return implode($mp_secret);
    }

    protected function updateWeixinMpFile($val)
    {
        $dir = $this->container->getParameter('kernel.root_dir').'/../web';
        array_map('unlink', glob($dir.'/MP_verify_*.txt'));
        if (!empty($val)) {
            file_put_contents($dir.'/MP_verify_'.$val.'.txt', $val);
        }
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }

    protected function getAuthService()
    {
        return $this->createService('User:AuthService');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
