<?php
namespace Topxia\Component\Payment\Quickpay;

use Topxia\Component\Payment\Request;

class QuickpayRequest extends Request {

    protected $url = 'Https://Pay.Heepay.com/ShortPay/SubmitOrder.aspx';
    protected $submitUrl = '';

    public function form()
    {
        $form = array();
        $form['method'] = 'get';
        $form['params'] = $this->convertParams($this->params);
        $form['action'] = $this->submitUrl;
        return $form;
    }
 
    public function signParams($params) {
        $params['key']=$this->options['secret'];
        $params['agent_id']=$this->options['key'];
        ksort($params);
        $sign = '';
        foreach ($params as $key => $value) {
            $sign .= $key . '=' . $value . '&';
        }
        $sign=trim($sign,"&");
        return md5(strtolower($sign));
    }

    protected function convertParams($params)
    {
        if(empty($params) ||!array_key_exists('mobile',$params)){
            throw new \RuntimeException(sprintf('参数传递错误。'));
        }
        $aesArr = array();
        $aesArr['version']=1;
        $aesArr['user_identity']='';
        $aesArr['hy_auth_uid']='';
        $aesArr['mobile']=$params['mobile'];
        $aesArr['device_type']=1;
        $aesArr['device_id']='';
        $aesArr['custom_page']=0;
        $aesArr['display']=1;
        if (!empty($params['returnUrl'])) {
            $aesArr['return_url']=$params['returnUrl'];
        }
        if (!empty($params['notifyUrl'])) {
            $aesArr['notify_url']=$params['notifyUrl'];
        }
        $aesArr['agent_bill_id']=$params['orderSn'];
        $aesArr['agent_bill_time']=date("YmdHis",time());
        $aesArr['pay_amt']=$params['amount'];
        $aesArr['goods_name']=mb_substr($this->filterText($params['title']), 0, 50, 'gb2312');
        $aesArr['goods_note']='';
        $aesArr['goods_num']=1;
        $aesArr['user_ip']=$this->get_client_ip();
        $aesArr['ext_param1']='';
        $aesArr['ext_param2']='';
        $aesArr['auth_card_type']=0;
        $aesArr['timestamp']=time()*1000;
        $sign = $this->signParams($aesArr);
        $encrypt_data = urlencode(base64_encode($this->Encrypt(http_build_query($aesArr),$this->options['aes'])));        
        $url = $this->url."?agent_id=".$this->options['key']."&encrypt_data=".$encrypt_data."&sign=".$sign;

        $result = $this->curlRequest($url);
        $xml = simplexml_load_string($result);
        $redir=(string)$xml->encrypt_data;
        $redirurl=$this->Decrypt($redir,$this->options['aes']);

        // parse_str($redirurl,$tip);
        // if(!array_key_exists('redirect_url', $tip)){
        //     throw new \RuntimeException(sprintf($tip['ret_msg']));
        // }
        // $this->submitUrl=$tip['redirect_url'];
        // unset($tip['ret_code'],$tip['ret_msg'],$tip['redirect_url']);
        // $converted = array();
        // foreach ($tip as $key => $value) {
        //     $converted[$key]=$value;
        // }

        $arr=explode('redirect_url=', $redirurl);



        //return $converted;


        // if(count($arr)<2){
        //     throw new \RuntimeException(sprintf('支付异常。'));
        // }
        //return $tip['redirect_url'];
        // echo $arr[1];
        // //header('Location:'.$arr[1]);
        echo "<script>top.location='".$arr[1]."'</script>";
        
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

    protected function filterText($text)
    {
        return str_replace(array('#', '%', '&', '+'), array('＃', '％', '＆', '＋'), $text);
    }

    private function get_client_ip()
    {
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
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
