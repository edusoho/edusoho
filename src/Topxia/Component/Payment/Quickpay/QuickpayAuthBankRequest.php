<?php
namespace Topxia\Component\Payment\Quickpay;

use Topxia\Component\Payment\Request;

class QuickpayAuthBankRequest extends Request 
{
	protected $url = 'Https://Pay.Heepay.com/API/ShortPay/ShortPayQueryAuthBanks.aspx';
	


	protected function signParams($params) {
		
        $signStr='';
        $signStr  = $signStr . 'agent_id=' . $this->options['key'];
        $signStr  = $signStr . '&key=' . $this->options['secret'];
        $signStr  = $signStr . '&timestamp=' . time()*1000;
        $signStr  = $signStr . '&user_identity=' .md5($this->options['account']."_".$params['userId']);
        $signStr  = $signStr . '&version=' . 1;
        $sign=md5(strtolower($signStr));

        return $sign;
    }

    protected function convertParams($params)
    {

    }

	public function getAuthBanks()
    {
        $authBankUrl = '';
        $converted = array();
        $converted['agent_id']=$this->options['key'];
        $converted['timestamp']=time()*1000;
        $converted['version']=1;
        $converted['user_identity']=md5($this->options['account']."_".$params['userId']);
        $encrypt_data = urlencode(base64_encode($this->Encrypt(http_build_query($converted),$this->options['aes'])));        

        $url = $authBankUrl."?agent_id=".$this->options['key']."&encrypt_data=".$encrypt_data."&sign=".$sign;
        $result = $this->curlRequest($url);
       // $result = $this->postRequest($authBankUrl,array('agent_id'=>$this->options['key'],'encrypt_data'=>$encrypt_data,'sign'=>$sign));
        $xml = simplexml_load_string($result);
        $redir=(string)$xml->encrypt_data;
        $redirurl=$this->Decrypt($redir,$this->options['aes']);
        parse_str($redirurl,$authBanks);

        return $this->getListBank($authBanks);
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
        if(array_key_exists('auth_uid_Info', $authBanks)){
            $authBanks['auth_uid_Info'] = trim($authBanks['auth_uid_Info'],';');
            $banks = explode(";",$authBanks['auth_uid_Info']);
            foreach ($banks as $key=>$bank) {
                $bankInfo = explode("_",$bank);
                $arr = array();
                foreach ($bankInfo as $value) {
                    $arr[]= $value;
                }
                list($user_bank[$key]['bankId'],$user_bank[$key]['bankName'],$user_bank[$key]['bankNumber'],$user_bank[$key]['bankType'],$user_bank[$key]['bankAuthorize'])=$arr;
            }
        }

        return $user_bank;
    }
}