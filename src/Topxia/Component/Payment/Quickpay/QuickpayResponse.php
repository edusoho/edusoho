<?php
namespace Topxia\Component\Payment\Quickpay;

use Topxia\Component\Payment\Payment;
use Topxia\Component\Payment\Response;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Util\Phpsec\Crypt\Rijndael;

class QuickpayResponse extends Response
{
    protected $url = 'Https://query.Heepay.com/ShortPay/QueryPay.aspx';

    public function getPayData()
    {
        $params = $this->params;
        $aesStr = $this->decrypt($params['encrypt_data'], $this->options['aes']);
        parse_str($aesStr, $returnArray);
        $result = $this->confirmSellerSendGoods($returnArray['agent_bill_id']);

        if (!in_array($result['status'], array('SUCCESS', 'WFPAYMENT', 'CANCEL'))) {
            throw new \RuntimeException('快捷支付失败');
        }

        $order = $this->getOrder($result["agent_bill_id"]);
        $this->createUserAuth('quickpay', $result, $order);

        $data            = array();
        $data['payment'] = 'quickpay';

        $data['sn'] = $order['sn'];

        if (in_array($result['status'], array('SUCCESS'))) {
            $data['status'] = 'success';
        } elseif (in_array($result['status'], array('CLOSED'))) {
            $data['status'] = 'closed';
        } elseif (in_array($result['status'], array('WFPAYMENT'))) {
            $data['status'] = 'created';
        } else {
            $data['status'] = 'unknown';
        }

        $data['amount'] = $returnArray['real_amt'];

        if (!empty($returnArray['hy_deal_time'])) {
            $data['paidTime'] = strtotime($returnArray['hy_deal_time']);
        } else {
            $data['paidTime'] = time();
        }

        $data['raw'] = $returnArray;

        return $data;
    }

    private function confirmSellerSendGoods($sn)
    {
        $params                     = $this->params;
        $converted                  = array();
        $converted['agent_bill_id'] = $sn;
        $converted['agent_id']      = $params['agent_id'];
        $converted['timestamp']     = time() * 1000;
        $converted['version']       = 1;

        $sign        = $this->signParams($converted);
        $aesArr      = array('version' => 1, 'agent_bill_id' => $sn, 'timestamp' => $converted['timestamp']);
        $encryptData = urlencode(base64_encode($this->encrypt(http_build_query($aesArr), $this->options['aes'])));

        $url      = $this->url."?agent_id=".$params['agent_id']."&encrypt_data=".$encryptData."&sign=".$sign;
        $result   = $this->curlRequest($url);
        $xml      = simplexml_load_string($result);
        $redir    = (string) $xml->encrypt_data;
        $redirurl = $this->decrypt($redir, $this->options['aes']);
        parse_str($redirurl, $tip);

        return $tip;
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

    private function signParams($params)
    {
        $signStr = '';
        $signStr = $signStr.'agent_bill_id='.$params['agent_bill_id'];
        $signStr = $signStr.'&agent_id='.$params['agent_id'];
        $signStr = $signStr.'&key='.$this->options['secret'];
        $signStr = $signStr.'&timestamp='.time() * 1000;
        $signStr = $signStr.'&version='. 1;
        $sign    = md5(strtolower($signStr));
        return $sign;
    }

    public function createUserAuth($name, $params, $order)
    {
        $authBankRequest = $this->createAuthBankRequest($name, $params, $order);
        $authBanks       = $authBankRequest->form();

        foreach ($authBanks as $authBank) {
            $bankAuth = $this->getUserService()->getUserPayAgreementByUserIdAndBankAuth($order['userId'], $authBank['bankAuth']);

            if (empty($bankAuth)) {
                $field = array(
                    'userId'      => $order['userId'],
                    'type'        => $authBank['type'],
                    'bankName'    => $authBank['bankName'],
                    'bankNumber'  => $authBank['bankNumber'],
                    'bankAuth'    => $authBank['bankAuth'],
                    'bankId'      => $authBank['bankId'],
                    'createdTime' => time()
                );
                $this->getUserService()->createUserPayAgreement($field);
            }
        }
    }

    protected function createAuthBankRequest($name, $params, $order)
    {
        $options = $this->getPaymentOptions($name);
        $request = Payment::createAuthBankRequest($name, $options);
        return $request->setParams(array('userId' => $order['userId']));
    }

    protected function getPaymentOptions($payment)
    {
        $settings = $this->getSettingService()->get('payment');
        $options  = array(
            'key'    => $settings["{$payment}_key"],
            'secret' => $settings["{$payment}_secret"],
            'aes'    => $settings["{$payment}_aes"]
        );

        return $options;
    }

    public function getOrder($token)
    {
        if (stripos($token, 'c') !== false) {
            $order = $this->getOrderService()->getOrderByToken($token);
        }

        if (stripos($token, 'o') !== false) {
            $order = $this->getCashOrdersService()->getOrderByToken($token);
        }

        return $order;
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

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCashOrdersService()
    {
        return $this->getServiceKernel()->createService('Cash.CashOrdersService');
    }
}
