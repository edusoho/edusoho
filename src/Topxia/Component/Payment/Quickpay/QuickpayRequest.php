<?php
namespace Topxia\Component\Payment\Quickpay;

use Topxia\Component\Payment\Request;
use Topxia\Service\Common\ServiceKernel;

class QuickpayRequest extends Request
{
    protected $url       = 'Https://Pay.Heepay.com/ShortPay/SubmitOrder.aspx';
    protected $submitUrl = '';

    public function form()
    {
        $form           = array();
        $form['method'] = 'get';
        $form['params'] = $this->convertParams($this->params);
        $form['action'] = $this->submitUrl;
        return $form;
    }

    public function signParams($params)
    {
        $params['key']      = $this->options['secret'];
        $params['agent_id'] = $this->options['key'];
        ksort($params);
        $sign = '';

        foreach ($params as $key => $value) {
            $sign .= $key.'='.$value.'&';
        }

        $sign = trim($sign, "&");
        return md5(strtolower($sign));
    }

    protected function convertParams($params)
    {
        $ismobile = $this->ismobile();
        var_dump($ismobile);
        exit();

        $converted                  = array();
        $converted['version']       = 1;
        $converted['user_identity'] = $this->options['key']."_".$params['userId'];
        $converted['hy_auth_uid']   = '';

        if (isset($params['authBank']['bankAuth'])) {
            $converted['hy_auth_uid'] = $params['authBank']['bankAuth'];
        }

        $converted['mobile'] = '';

        if ($ismobile == 'Android') {
            $converted['device_type'] = 2;
        } elseif ($ismobile == 'iPhone' || $ismobile == 'iPad') {
            $converted['device_type'] = 0;
        } else {
            $converted['device_type'] = 1;
        }

        $converted['device_id']   = '';
        $converted['custom_page'] = 0;

        if ($ismobile == 'pc') {
            $converted['display'] = 1;
        } else {
            $converted['display'] = 0;
        }

        if (!empty($params['returnUrl'])) {
            $converted['return_url'] = $params['returnUrl'];
        }

        if (!empty($params['notifyUrl'])) {
            $converted['notify_url'] = $params['notifyUrl'];
        }

        $converted['agent_bill_id']   = $this->generateOrderToken();
        $converted['agent_bill_time'] = date("YmdHis", time());
        $converted['pay_amt']         = $params['amount'];
        $converted['goods_name']      = mb_substr($this->filterText($params['title']), 0, 50, 'utf-8');
        $converted['goods_note']      = '';
        $converted['goods_num']       = 1;
        $converted['user_ip']         = $this->getClientIp();
        $converted['ext_param1']      = '';
        $converted['ext_param2']      = '';
        $converted['auth_card_type']  = -1;
        $converted['timestamp']       = time() * 1000;
        $sign                         = $this->signParams($converted);
        $encrypt_data                 = urlencode(base64_encode($this->Encrypt(http_build_query($converted), $this->options['aes'])));
        $url                          = $this->url."?agent_id=".$this->options['key']."&encrypt_data=".$encrypt_data."&sign=".$sign;
        $result                       = $this->curlRequest($url);
        $xml                          = simplexml_load_string($result);

        $redir    = (string) $xml->encrypt_data;
        $redirurl = $this->Decrypt($redir, $this->options['aes']);

        parse_str($redirurl, $tip);

        $this->updateBankAuth($params['orderSn'], $tip);

        $this->submitUrl = $tip['redirect_url'];
        unset($tip['ret_code'], $tip['ret_msg'], $tip['redirect_url']);
        $converted             = array();
        $converted['agent_id'] = $this->options['key'];

        foreach ($tip as $key => $value) {
            $converted[$key] = $value;
        }

        return $converted;
    }

    private function generateOrderToken()
    {
        return 'h'.date('YmdHis', time()).mt_rand(10000, 99999);
    }

    public function updateBankAuth($sn, $params)
    {
        $order     = $this->getOrderService()->getOrderBySn($sn);
        $userAuth  = array('hy_auth_uid' => $params['hy_auth_uid'], 'hy_token_id' => $params['hy_token_id']);
        $userAuth  = json_encode($userAuth);
        $authBanks = $this->getUserService()->findUserPayAgreementsByUserId($order['userId']);

        if (!empty($authBanks)) {
            foreach ($authBanks as $authBank) {
                $this->getUserService()->updateUserPayAgreementByBankAuth($authBank['bankAuth'], array('userAuth' => $userAuth, 'updatedTime' => time()));
            }
        }
    }

    public function isMobile()
    {
        $useragent               = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $useragent_commentsblock = preg_match('|\(.*?\)|', $useragent, $matches) > 0 ? $matches[0] : '';
        function CheckSubstrs($substrs, $text)
        {
            foreach ($substrs as $substr) {
                if (false !== strpos($text, $substr)) {
                    return true;
                }
            }

            return false;
        }

        $mobile_os_list    = array('Google Wireless Transcoder', 'Windows CE', 'WindowsCE', 'Symbian', 'Android', 'armv6l', 'armv5', 'Mobile', 'CentOS', 'mowser', 'AvantGo', 'Opera Mobi', 'J2ME/MIDP', 'Smartphone', 'Go.Web', 'Palm', 'iPAQ');
        $mobile_token_list = array('Profile/MIDP', 'Configuration/CLDC-', '160×160', '176×220', '240×240', '240×320', '320×240', 'UP.Browser', 'UP.Link', 'SymbianOS', 'PalmOS', 'PocketPC', 'SonyEricsson', 'Nokia', 'BlackBerry', 'Vodafone', 'BenQ', 'Novarra-Vision', 'Iris', 'NetFront', 'HTC_', 'Xda_', 'SAMSUNG-SGH', 'Wapaka', 'DoCoMo', 'iPhone', 'iPod');

        $found_mobile = CheckSubstrs($mobile_os_list, $useragent_commentsblock) ||
        CheckSubstrs($mobile_token_list, $useragent);

        if ($found_mobile) {
            return true;
        } else {
            return false;
        }
    }

    private function curlRequest($url)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Topxia Payment Client 1.0');
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    protected function filterText($text)
    {
        return str_replace(array('#', '%', '&', '+'), array('＃', '％', '＆', '＋'), $text);
    }

    private function Encrypt($data, $key)
    {
        $decodeKey = base64_decode($key);
        $iv        = substr($decodeKey, 0, 16);
        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $decodeKey, $data, MCRYPT_MODE_CBC, $iv);
        return $encrypted;
    }

    private function Decrypt($data, $key)
    {
        $decodeKey = base64_decode($key);
        $data      = base64_decode($data);
        $iv        = substr($decodeKey, 0, 16);
        $encrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $decodeKey, $data, MCRYPT_MODE_CBC, $iv);

        return $encrypted;
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
