<?php

namespace Topxia\MobileBundleV2\Alipay;

require_once 'lib/alipay_core.function.php';
require_once 'lib/alipay_rsa.function.php';
require_once 'lib/alipay_md5.function.php';

class AlipayNotify
{
    /**
     * HTTPS形式消息验证地址
     */
    public $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    /**
     * HTTP形式消息验证地址
     */
    public $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';
    public $alipay_config;

    public function __construct($alipay_config)
    {
        $this->alipay_config = $alipay_config;
    }

    public function AlipayNotify($alipay_config)
    {
        $this->__construct($alipay_config);
    }

    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息.
     *
     * @return 验证结果
     */
    public function verifyNotify()
    {
        if (empty($_POST)) {
            //判断POST来的数组是否为空
            return false;
        } else {
            //对notify_data解密
            $decrypt_post_para = $_POST;
            if ($this->alipay_config['sign_type'] == '0001') {
                $decrypt_post_para['notify_data'] = rsaDecrypt($decrypt_post_para['notify_data'], $this->alipay_config['private_key_path']);
            }

            //notify_id从decrypt_post_para中解析出来（也就是说decrypt_post_para中已经包含notify_id的内容）
            $doc = new \DOMDocument();
            $doc->loadXML($decrypt_post_para['notify_data']);
            $notify_id = $doc->getElementsByTagName('notify_id')->item(0)->nodeValue;

            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'true';
            if (!empty($notify_id)) {
                $responseTxt = $this->getResponse($notify_id);
            }

            //生成签名结果
            $isSign = $this->getSignVeryfy($decrypt_post_para, $_POST['sign'], false);

            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match('/true$/i', $responseTxt) && $isSign) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 针对return_url验证消息是否是支付宝发出的合法消息.
     *
     * @return 验证结果
     */
    public function verifyReturn()
    {
        if (empty($_GET)) {
            //判断GET来的数组是否为空
            return false;
        } else {
            //生成签名结果
            $isSign = $this->getSignVeryfy($_GET, $_GET['sign'], true);

            //写日志记录

            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if ($isSign) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 解密.
     *
     * @param  $input_para       要解密数据
     *
     * @return 解密后结果
     */
    public function decrypt($prestr)
    {
        return rsaDecrypt($prestr, trim($this->alipay_config['private_key_path']));
    }

    /**
     * 异步通知时，对参数做固定排序.
     *
     * @param  $para                   排序前的参数组
     *
     * @return 排序后的参数组
     */
    public function sortNotifyPara($para)
    {
        $para_sort['service'] = $para['service'];
        $para_sort['v'] = $para['v'];
        $para_sort['sec_id'] = $para['sec_id'];
        $para_sort['notify_data'] = $para['notify_data'];

        return $para_sort;
    }

    /**
     * 获取返回时的签名验证结果.
     *
     * @param  $para_temp           通知返回来的参数数组
     * @param  $sign                返回的签名结果
     * @param  $isSort              是否对待签名数组排序
     *
     * @return 签名验证结果
     */
    public function getSignVeryfy($para_temp, $sign, $isSort)
    {
        //除去待签名参数数组中的空值和签名参数
        $para = paraFilter($para_temp);

        //对待签名参数数组排序
        if ($isSort) {
            $para = argSort($para);
        } else {
            $para = $this->sortNotifyPara($para);
        }

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = createLinkstring($para);

        $isSgin = false;
        switch (strtoupper(trim($this->alipay_config['sign_type']))) {
            case 'MD5':
                $isSgin = md5Verify($prestr, $sign, $this->alipay_config['key']);
                break;
            case 'RSA':
            case '0001':
                $isSgin = rsaVerify($prestr, trim($this->alipay_config['ali_public_key_path']), $sign);
                break;
            default:
                $isSgin = false;
        }

        return $isSgin;
    }

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     *
     * @param  $notify_id           通知校验ID
     *
     * @return 服务器ATN结果
     */
    public function getResponse($notify_id)
    {
        $transport = strtolower(trim($this->alipay_config['transport']));
        $partner = trim($this->alipay_config['partner']);
        $veryfy_url = '';
        if ($transport == 'https') {
            $veryfy_url = $this->https_verify_url;
        } else {
            $veryfy_url = $this->http_verify_url;
        }
        $veryfy_url = $veryfy_url.'partner='.$partner.'&notify_id='.$notify_id;
        $responseTxt = getHttpResponseGET($veryfy_url, $this->alipay_config['cacert']);

        return $responseTxt;
    }
}
