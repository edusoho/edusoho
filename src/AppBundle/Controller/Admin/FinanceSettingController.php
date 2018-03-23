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
            'heepay_enabled' => 0,
            'heepay_key' => '',
            'heepay_secret' => '',
            'quickpay_enabled' => 0,
            'quickpay_key' => '',
            'quickpay_secret' => '',
            'quickpay_aes' => '',
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
            $payment = $this->isClosePayment($payment);
            $formerPayment = $this->getSettingService()->get('payment');
            if (0 == $payment['enabled'] && 1 == $formerPayment['enabled']) {
                $payment['alipay_enabled'] = 0;
                $payment['wxpay_enabled'] = 0;
                $payment['heepay_enabled'] = 0;
                $payment['quickpay_enabled'] = 0;
                $payment['llpay_enabled'] = 0;
            }

            //新增支付方式，加入下列列表计算，以便判断是否关闭支付功能
            $this->getSettingService()->set('payment', $payment);
            $this->updateWeixinMpFile($payment['wxpay_mp_secret']);
            $this->getLogService()->info('system', 'update_settings', '更改支付方式设置', $payment);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin/system/payment.html.twig', array(
            'payment' => $payment,
        ));
    }

    public function isClosePayment($payment)
    {
        $payments = ArrayToolkit::parts($payment, array('alipay_enabled', 'wxpay_enabled', 'heepay_enabled', 'quickpay_enabled', 'llpay_enabled'));
        $sum = 0;
        foreach ($payments as $value) {
            $sum += $value;
        }

        if ($sum < 1) {
            $payment['enabled'] = 0;
        } else {
            $payment['enabled'] = 1;
        }

        return $payment;
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
            $this->getLogService()->info('system', 'update_settings', '更新退款设置', $refundSetting);
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
