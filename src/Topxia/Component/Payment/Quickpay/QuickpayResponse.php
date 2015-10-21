<?php
namespace Topxia\Component\Payment\Quickpay;

use Topxia\Component\Payment\Response;

class QuickpayResponse extends Response
{

    protected $url = 'Https://query.Heepay.com/ShortPay/QueryPay.aspx';
    
    public function getPayData()
    {

        $params = $this->params;
        $aesStr= $this->Decrypt($params['encrypt_data'],$this->options['aes']);
        parse_str($aesStr,$returnArray);

        $result = $this->confirmSellerSendGoods($returnArray['agent_bill_id']);
        // if ($result['status'] != 'SUCCESS' || $result['status'] != 'WFPAYMENT' || $result['status'] != 'CANCEL'){
        //     throw new \RuntimeException('快捷支付失败');
        // }

        $data = array();
        $data['payment'] = 'Quickpay';
        $data['sn'] = $returnArray['agent_bill_id'];
        if(in_array($result['status'], array('SUCCESS'))) {
            $data['status'] = 'success';
        } else if (in_array($result['status'], array('CLOSED'))) {
            $data['status'] = 'closed';
        } else if (in_array($result['status'], array('WFPAYMENT'))) {
            $data['status'] = 'created';
        } else {
            $data['status'] = 'unknown';
        }
        $data['amount'] = $returnArray['real_amt'];
        
        if (!empty($returnArray['hy_deal_time'])) {
            $data['paidTime'] = $returnArray['hy_deal_time'];
        }else {
            $data['paidTime'] = time();
        }

        $data['raw'] = $returnArray;

        return $data;
        // $data['payment'] = 'heepay';
        // $data['sn'] = $params['agent_bill_id'];
        // $result = $this->confirmSellerSendGoods();
        // $returnArray = $this->toArray($result);
        // if ($returnArray['result'] != 1) {
        //     throw new \RuntimeException('网银支付失败');
        // }
        // if(in_array($returnArray['result'], array(1))) {
        //     $data['status'] = 'success';
        // } else {
        //     $data['status'] = 'unknown';
        // }
        // $data['amount'] = $params['pay_amt'];
        // $data['paidTime'] = time();

        // $data['raw'] = $params;

        // return $data;
    }

    private function confirmSellerSendGoods($sn)
    {
        $params = $this->params;
        $data = array();
        $data['agent_bill_id']=$sn;
        $data['agent_id']=$params['agent_id'];
        $data['timestamp']=time()*1000;
        $data['version']=1;
        $sign=$this->signParams($data);
        $aesArr = array('version'=>1,'agent_bill_id'=>$sn,'timestamp'=>$data['timestamp']);
        $encrypt_data = urlencode(base64_encode($this->Encrypt(http_build_query($aesArr),$this->options['aes'])));

        $url = $this->url."?agent_id=".$params['agent_id']."&encrypt_data=".$encrypt_data."&sign=".$sign;

        $result = $this->curlRequest($url);
        $xml = simplexml_load_string($result);
        $redir=(string)$xml->encrypt_data;
        $redirurl=$this->Decrypt($redir,$this->options['aes']);
        parse_str($redirurl,$tip);
        return $tip;
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

    private function signParams($params) {
        $signStr='';
        $signStr  = $signStr . 'agent_bill_id=' . $params['agent_bill_id'];
        $signStr  = $signStr . '&agent_id=' . $params['agent_id'];
        $signStr  = $signStr . '&key=' . $this->options['secret'];
        $signStr  = $signStr . '&timestamp=' . time()*1000;
        $signStr  = $signStr . '&version=' . 1;
        $sign=md5(strtolower($signStr));
        return $sign;
    }

    private function Encrypt($data,$key){
        $decodeKey = base64_decode($key);
        $iv     = substr($decodeKey,0,16);
        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $decodeKey, $data, MCRYPT_MODE_CBC, $iv);  
        return $encrypted;
    }

    private function Decrypt($data,$key){

        $decodeKey = base64_decode($key);
        $data = base64_decode($data);
        $iv = substr($decodeKey,0,16);
        $encrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $decodeKey, $data, MCRYPT_MODE_CBC, $iv); 

        return $encrypted;
    }
}