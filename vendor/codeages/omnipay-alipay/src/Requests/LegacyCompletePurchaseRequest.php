<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Responses\LegacyCompletePurchaseResponse;
use Omnipay\Common\Message\ResponseInterface;

class LegacyCompletePurchaseRequest extends AbstractLegacyRequest
{
    protected $verifyNotifyId = true;


    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->getParams();
    }


    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->getParameter('params');
    }


    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return ResponseInterface
     */
    public function sendData($data)
    {
        if (array_get($data, 'result')) {
            $request = new LegacyVerifyAppPayReturnRequest($this->httpClient, $this->httpRequest);
            $request->initialize($this->parameters->all());
            $request->setResult($data['result']);
            $request->setAlipayPublicKey($this->getAlipayPublicKey());
            $data = $request->send()->getData();

            $data = array_map(
                function ($v) {
                    return substr($v, 1, mb_strlen($v) - 2) . '';
                },
                $data
            );

            if (array_get($data, 'success') == 'true') {
                $data['trade_status'] = 'TRADE_SUCCESS';
            } else {
                $data['trade_status'] = 'WAIT_BUYER_PAY';
            }
        } else {
            $request = new LegacyNotifyRequest($this->httpClient, $this->httpRequest);
            $request->initialize($this->parameters->all());
            $request->setAlipayPublicKey($this->getAlipayPublicKey());
            $request->setVerifyNotifyId($this->verifyNotifyId);
            $request->setKey($this->getKey());
            $response = $request->send();

            $data = $response->getData();
        }

        return $this->response = new LegacyCompletePurchaseResponse($this, $data);
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setParams($value)
    {
        return $this->setParameter('params', $value);
    }


    /**
     * @param boolean $verifyNotifyId
     *
     * @return \Omnipay\Alipay\Requests\LegacyCompletePurchaseRequest
     */
    public function setVerifyNotifyId($verifyNotifyId)
    {
        $this->verifyNotifyId = $verifyNotifyId;

        return $this;
    }
}
