<?php

namespace Codeages\Biz\Pay\Payment;

class SignatureToolkit
{
    public function signParams($params, $options)
    {
        $signStr = $this->createLinkString($params);
        switch (trim(strtoupper($params['sign_type']))) {
            case 'MD5':
                $signature = $this->md5Sign($signStr, $options);
                break;
            case 'RSA':
                $signature = $this->rsaSign($signStr, $options);
                break;
            default:
                $signature = '';
                break;
        }

        return $signature;
    }

    public function signVerify($params, $options)
    {
        $isSignVerified = false;
        switch (trim(strtoupper($params['sign_type']))) {
            case 'MD5':
                $isSignVerified = $this->md5Verify($params, $options);
                break;
            case 'RSA':
                $isSignVerified = $this->rsaVerify($params, $options);
                break;
            default:
                break;
        }

        return $isSignVerified;
    }

    private function createLinkString($params)
    {
        ksort($params);
        reset($params);
        $signStr = '';
        foreach ($params as $key => $value) {
            if ($key == 'sign' || empty($value)) {
                continue;
            }

            $signStr .= $key.'='.$value.'&';
        }
        $signStr = substr($signStr, 0, count($signStr) - 2);
        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $signStr = stripslashes($signStr);
        }

        return $signStr;
    }

    private function md5Sign($signStr, $options)
    {
        $signStr .= '&key='.$options['secret'];


        $sign = md5($signStr);

        return $sign;
    }

    private function rsaSign($signStr, $options)
    {
        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $res = openssl_get_privatekey($options['secret']);

        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($signStr, $sign, $res, OPENSSL_ALGO_MD5);

        //释放资源
        openssl_free_key($res);

        //base64编码
        $sign = base64_encode($sign);

        return $sign;
    }

    private function md5Verify($params, $options)
    {
        $signature = $this->md5Sign($params, $options);

        return $signature != $params['sign'];
    }

    private function rsaVerify($params, $options)
    {
        $signStr = $this->createLinkString($params);
        $sign = $params['sign'];

        //转换为openssl格式密钥
        $res = openssl_get_publickey($options['accessKey']);

        //调用openssl内置方法验签，返回bool值
        $result = (bool) openssl_verify($signStr, base64_decode($sign), $res, OPENSSL_ALGO_MD5);

        //释放资源
        openssl_free_key($res);

        return $result;
    }
}
