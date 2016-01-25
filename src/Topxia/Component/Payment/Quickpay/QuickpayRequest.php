<?php
namespace Topxia\Component\Payment\Quickpay;

use Topxia\Component\Payment\Request;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Util\Phpsec\Crypt\Rijndael;
use Topxia\Service\Order\OrderProcessor\OrderProcessorFactory;

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
        $isMobile   = $this->isMobile($params['userAgent']);
        $mobileType = $this->mobileType($params['userAgent']);

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

        $converted['agent_bill_id']   = $this->generateOrderToken($params);
        $converted['agent_bill_time'] = date("YmdHis", time());
        $converted['pay_amt']         = $params['amount'];
        $converted['goods_name']      = mb_substr($this->filterText($params['targetTitle']), 0, 15, 'utf-8');

        if (strlen($converted['goods_name']) >= 45) {
            $converted['goods_name'] .= '...';
        }

        $converted['goods_note'] = mb_substr(urldecode(str_replace('%C2%A0', '', urlencode($this->filterText(str_replace('&nbsp;', '', $params['note']))))), 0, 50, 'utf-8');

        if (strlen($converted['goods_note']) >= 50) {
            $converted['goods_note'] .= '...';
        }

        $converted['goods_num']      = 1;
        $converted['user_ip']        = $this->getClientIp();
        $converted['ext_param1']     = '';
        $converted['ext_param2']     = '';
        $converted['auth_card_type'] = -1;
        $converted['timestamp']      = time() * 1000;
        $sign                        = $this->signParams($converted);
        $encryptData                 = urlencode(base64_encode($this->encrypt(http_build_query($converted), $this->options['aes'])));
        $url                         = $this->url."?agent_id=".$this->options['key']."&encrypt_data=".$encryptData."&sign=".$sign;
        $result                      = $this->curlRequest($url);
        $xml                         = simplexml_load_string($result);

        $redir    = (string) $xml->encrypt_data;
        $redirurl = $this->decrypt($redir, $this->options['aes']);

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

    private function generateOrderToken($params)
    {
        $processor = OrderProcessorFactory::create($params['targetType']);
        return $processor->generateOrderToken();
    }

    public function updateBankAuth($sn, $params)
    {
        $order     = $this->getOrderService()->getOrderBySn($sn);
        $userAuth  = array('hy_auth_uid' => $params['hy_auth_uid'], 'hy_token_id' => $params['hy_token_id']);
        $userAuth  = json_encode($userAuth);
        $authBanks = $this->getUserService()->findUserPayAgreementsByUserId($order['userId']);

        if (!empty($authBanks)) {
            foreach ($authBanks as $authBank) {
                $this->getUserService()->updateUserPayAgreementByUserIdAndBankAuth($order['userId'], $authBank['bankAuth'], array('userAuth' => $userAuth, 'updatedTime' => time()));
            }
        }
    }

    public function isMobile($userAgent)
    {
        if (strpos($userAgent, 'iPhone') || strpos($userAgent, 'iPad') || strpos($userAgent, 'Android')) {
            return true;
        } else {
            return false;
        }
    }

    public function mobileType($userAgent)
    {
        if (strpos($userAgent, 'iPhone') || strpos($userAgent, 'iPad')) {
            return 'IOS';
        }

        if (strpos($userAgent, 'Android')) {
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
        return str_replace(array('#', '%', '&', '+', '《', '》'), array('＃', '％', '＆', '＋', '', ''), $text);
    }

    private function encrypt($data, $key)
    {
        $decodeKey = base64_decode($key);
        $iv        = substr($decodeKey, 0, 16);

        $rijndael = new Rijndael();
        $rijndael->setIV($iv);
        $rijndael->setKey($decodeKey);
        $rijndael->disablePadding();

        $length = strlen($data);
        $pad    = 16 - ($length % 16);
        $data   = str_pad($data, $length + $pad, "\0");

        $encrypted = $rijndael->encrypt($data);

        return $encrypted;
    }

    private function decrypt($data, $key)
    {
        $decodeKey = base64_decode($key);
        $data      = base64_decode($data);
        $iv        = substr($decodeKey, 0, 16);

        $rijndael = new Rijndael();
        $rijndael->setIV($iv);
        $rijndael->setKey($decodeKey);
        $rijndael->disablePadding();
        $encrypted = $rijndael->decrypt($data);

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
