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
        $isMobile = $this->isMobile();

        $mobileType = $this->mobileType();

        $converted                  = array();
        $converted['version']       = 1;
        $converted['user_identity'] = $this->options['key']."_".$params['userId'];
        $converted['hy_auth_uid']   = '';

        if (isset($params['authBank']['bankAuth'])) {
            $converted['hy_auth_uid'] = $params['authBank']['bankAuth'];
        }

        $converted['mobile'] = '';

        if ($mobileType == 'Android') {
            $converted['device_type'] = 2;
        } elseif ($mobileType == 'IOS') {
            $converted['device_type'] = 0;
        } else {
            $converted['device_type'] = 1;
        }

        $converted['device_id']   = '';
        $converted['custom_page'] = 0;

        if ($isMobile) {
            $converted['display'] = 0;
        } else {
            $converted['display'] = 1;
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
        if (isset($_SERVER['HTTP_VIA'])) {
            return true;
        }

        if (isset($_SERVER['HTTP_X_NOKIA_CONNECTION_MODE'])) {
            return true;
        }

        if (isset($_SERVER['HTTP_X_UP_CALLING_LINE_ID'])) {
            return true;
        }

        if (strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML") > 0) {
            // Check whether the browser/gateway says it accepts WML.
            $br = "WML";
        } else {
            $browser = isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '';

            if (empty($browser)) {
                return true;
            }

            $browser = substr($browser, 0, 4);

            if ($browser == "Noki" || // Nokia phones and emulators
                $browser == "Eric" || // Ericsson WAP phones and emulators
                $browser == "WapI" || // Ericsson WapIDE 2.0
                $browser == "MC21" || // Ericsson MC218
                $browser == "AUR" || // Ericsson R320
                $browser == "R380" || // Ericsson R380
                $browser == "UP.B" || // UP.Browser
                $browser == "WinW" || // WinWAP browser
                $browser == "UPG1" || // UP.SDK 4.0
                $browser == "upsi" || // another kind of UP.Browser ??
                $browser == "QWAP" || // unknown QWAPPER browser
                $browser == "Jigs" || // unknown JigSaw browser
                $browser == "Java" || // unknown Java based browser
                $browser == "Alca" || // unknown Alcatel-BE3 browser (UP based?)
                $browser == "MITS" || // unknown Mitsubishi browser
                $browser == "MOT-" || // unknown browser (UP based?)
                $browser == "My S" || // unknown Ericsson devkit browser ?
                $browser == "WAPJ" || // Virtual WAPJAG www.wapjag.de
                $browser == "fetc" || // fetchpage.cgi Perl script from www.wapcab.de
                $browser == "ALAV" || // yet another unknown UP based browser ?
                $browser == "Wapa" || // another unknown browser (Web based "Wapalyzer"?)
                $browser == "Oper") // Opera
            {
                $br = "WML";
            } else {
                $br = "HTML";
            }
        }

        if ($br == "WML") {
            return true;
        } else {
            return false;
        }
    }

    public function mobileType()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
            return 'IOS';
        }

        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Android')) {
            return 'Android';
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
