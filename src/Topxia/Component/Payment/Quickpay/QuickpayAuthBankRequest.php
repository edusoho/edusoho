<?php
namespace Topxia\Component\Payment\Quickpay;

use Topxia\Component\Payment\Request;
use Topxia\Service\Util\Phpsec\Crypt\Rijndael;

class QuickpayAuthBankRequest extends Request
{
    protected $url = 'Https://Pay.Heepay.com/API/ShortPay/ShortPayQueryAuthBanks.aspx';

    public function form()
    {
        $encryptData = $this->convertParams($this->params);
        $sign        = $this->signParams($this->params);
        $url         = $this->url."?agent_id=".$this->options['key']."&encrypt_data=".$encryptData."&sign=".$sign;
        $result      = $this->curlRequest($url);

        $xml      = simplexml_load_string($result);
        $redir    = (string) $xml->encrypt_data;
        $redirurl = $this->decrypt($redir, $this->options['aes']);
        parse_str($redirurl, $authBanks);

        return $this->getListBank($authBanks);
    }

    public function signParams($params)
    {
        $signStr = '';
        $signStr = $signStr.'agent_id='.$this->options['key'];
        $signStr = $signStr.'&key='.$this->options['secret'];
        $signStr = $signStr.'&timestamp='.time() * 1000;
        $signStr = $signStr.'&user_identity='.$this->options['key']."_".$params['userId'];
        $signStr = $signStr.'&version='. 1;
        $sign    = md5(strtolower($signStr));

        return $sign;
    }

    protected function convertParams($params)
    {
        $converted                  = array();
        $converted['agent_id']      = $this->options['key'];
        $converted['timestamp']     = time() * 1000;
        $converted['version']       = 1;
        $converted['user_identity'] = $this->options['key']."_".$params['userId'];
        $encryptData                = urlencode(base64_encode($this->encrypt(http_build_query($converted), $this->options['aes'])));

        return $encryptData;
    }

    private function curlRequest($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function getListBank($authBanks)
    {
        $user_bank = array();

        if (array_key_exists('auth_uid_Info', $authBanks)) {
            $authBanks['auth_uid_Info'] = trim($authBanks['auth_uid_Info'], ';');
            $banks                      = explode(";", $authBanks['auth_uid_Info']);

            foreach ($banks as $key => $bank) {
                $bankInfos = explode("_", $bank);
                $data      = array();

                foreach ($bankInfos as $bankInfo) {
                    $data[] = $bankInfo;
                }

                list($user_bank[$key]['bankId'], $user_bank[$key]['bankName'], $user_bank[$key]['bankNumber'], $user_bank[$key]['type'], $user_bank[$key]['bankAuth']) = $data;
            }
        }

        return $user_bank;
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
}
