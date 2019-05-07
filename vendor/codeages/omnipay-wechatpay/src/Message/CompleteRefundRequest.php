<?php

namespace Omnipay\WechatPay\Message;

use Omnipay\WechatPay\Helper;

class CompleteRefundRequest extends BaseAbstractRequest
{

    public function setRequestParams($requestParams)
    {
        $this->setParameter('request_params', $requestParams);
    }

    public function sendData($data)
    {
        $data = $this->getData();
        return $this->response = new CompleteRefundResponse($this, $data);
    }

    public function getData()
    {
        $data = $this->getRequestParams();

        if (is_string($data)) {
            $data = Helper::xml2array($data);
        }

        $decryptData = $this->decryptData($data['req_info'], $this->getApiKey());
        $data['req_info'] = $decryptData;
        return $data;
    }

    protected function decryptData($encryptData, $key = '')
    {
        $md5LowerKey = strtolower(md5($key));
        $decrypted = openssl_decrypt($encryptData, "AES-256-ECB", $md5LowerKey);
        return Helper::xml2array($decrypted);
    }

    public function getRequestParams()
    {
        return $this->getParameter('request_params');
    }
}
