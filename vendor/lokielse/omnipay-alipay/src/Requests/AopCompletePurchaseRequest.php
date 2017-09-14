<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Responses\AopCompletePurchaseResponse;
use Omnipay\Alipay\Responses\AopTradeQueryResponse;
class AopCompletePurchaseRequest extends \Omnipay\Alipay\Requests\AbstractAopRequest
{
    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */
    public function getData()
    {
        $this->validateParams();
        return $this->getParams();
    }
    public function validateParams()
    {
        $this->validate('params');
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
     * @return AopCompletePurchaseResponse
     */
    public function sendData($data)
    {
        if (isset($data['result'])) {
            $request = new \Omnipay\Alipay\Requests\AopVerifyAppPayReturnRequest($this->httpClient, $this->httpRequest);
            $request->initialize($this->parameters->all());
            $request->setEndpoint($this->getEndpoint());
            $request->setResult($data['result']);
            $request->setAlipayPublicKey($this->getAlipayPublicKey());
            $data = $request->send()->getData();
        } else {
            $request = new \Omnipay\Alipay\Requests\AopNotifyRequest($this->httpClient, $this->httpRequest);
            $request->initialize(array('params' => $data));
            $request->setEndpoint($this->getEndpoint());
            $request->setAlipayPublicKey($this->getAlipayPublicKey());
            $data = $request->send()->getData();
            if (!array_get($data, 'trade_status')) {
                $tn = array_get($data, 'trade_no');
                $request = new \Omnipay\Alipay\Requests\AopTradeQueryRequest($this->httpClient, $this->httpRequest);
                $request->initialize($this->getParameters());
                $request->setEndpoint($this->getEndpoint());
                $request->setBizContent(array('trade_no' => $tn));
                $request->setPrivateKey($this->getPrivateKey());
                /**
                 * @var AopTradeQueryResponse $response
                 */
                $response = $request->send();
                $tradeStatus = $response->getAlipayResponse('trade_status');
                $data['trade_status'] = $tradeStatus;
            }
        }
        return $this->response = new \Omnipay\Alipay\Responses\AopCompletePurchaseResponse($this, $data);
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
}