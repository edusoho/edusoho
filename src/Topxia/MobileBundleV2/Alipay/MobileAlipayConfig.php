<?php

namespace Topxia\MobileBundleV2\Alipay;

use Topxia\Service\Common\ServiceKernel;

class MobileAlipayConfig
{
    public static $config;

    public static function getAlipayConfig($name)
    {
        if (!isset(self::$config[$name])) {
            $alipay_config = self::initAlipayConfig($name);
            self::$config[$name] = $alipay_config;
        }

        return $alipay_config;
    }

    public static function createAlipayOrderUrl($request, $name, $order)
    {
        $alipay_config = self::getAlipayConfig($name);

        $payUrl = $request->getSchemeAndHttpHost().'/mapi_v2/alipay_pay?WIDseller_email='.$alipay_config['seller_email'];
        $payUrl = $payUrl.'&WIDout_trade_no='.$order['sn'];
        $payUrl = $payUrl.'&WIDsubject='.$order['title'];
        $payUrl = $payUrl.'&WIDtotal_fee='.$order['amount'];

        return $payUrl;
    }

    protected static function initAlipayConfig($name)
    {
        $payment = ServiceKernel::instance()->createService('System:SettingService')->get('payment', array());

        $alipay_config = array();

        $alipay_config['seller_email'] = empty($payment['alipay_account']) ? '' : $payment['alipay_account'];

        $alipay_config['partner'] = empty($payment['alipay_key']) ? '' : $payment['alipay_key'];

        //安全检验码，以数字和字母组成的32位字符
        //如果签名方式设置为“MD5”时，请设置该参数
        $alipay_config['key'] = empty($payment['alipay_secret']) ? '' : $payment['alipay_secret'];

        //商户的私钥（后缀是.pen）文件相对路径
        //如果签名方式设置为“0001”时，请设置该参数
        // $alipay_config['private_key_path'] = 'key/rsa_private_key.pem';

        // //支付宝公钥（后缀是.pen）文件相对路径
        // //如果签名方式设置为“0001”时，请设置该参数
        // $alipay_config['ali_public_key_path'] = 'key/alipay_public_key.pem';

        //签名方式 不需修改
        $alipay_config['sign_type'] = 'MD5';

        //字符编码格式 目前支持 gbk 或 utf-8
        $alipay_config['input_charset'] = 'utf-8';

        //ca证书路径地址，用于curl中ssl校验
        //请保证cacert.pem文件在当前文件夹目录中
        $alipay_config['cacert'] = getcwd().'\\cacert.pem';

        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $alipay_config['transport'] = 'http';

        return $alipay_config;
    }
}
