<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Responses\LegacyQueryResponse;
use Omnipay\Common\Message\ResponseInterface;

/**
 * Class LegacyQueryRequest
 * @package Omnipay\Alipay\Requests
 * @link    http://aopsdkdownload.cn-hangzhou.alipay-pub.aliyun-inc.com/demo/alipaysinglequery.zip
 */
class LegacyQueryRequest extends AbstractLegacyRequest
{

    protected $service = 'single_trade_query';


    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return ResponseInterface
     */
    public function sendData($data)
    {
        $url = sprintf('%s?%s', $this->getEndpoint(), http_build_query($this->getData()));

        $result = $this->httpClient->get($url)->send()->getBody();

        $xml  = simplexml_load_string($result);
        $json = json_encode($xml);
        $data = json_decode($json, true);

        return $this->response = new LegacyQueryResponse($this, $data);
    }


    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */
    public function getData()
    {
        $this->validateParams();

        $data = [
            'service'        => $this->service,
            'partner'        => $this->getPartner(),
            'trade_no'       => $this->getTradeNo(),
            'out_trade_no'   => $this->getOutTradeNo(),
            '_input_charset' => $this->getInputCharset()
        ];
        $data['sign'] = $this->sign($data, $this->getSignType());
        
        return $data;
    }


    protected function validateParams()
    {
        $this->validate(
            'partner',
            '_input_charset'
        );

        $this->validateOne(
            'trade_no',
            'out_trade_no'
        );
    }


    /**
     * @return mixed
     */
    public function getTradeNo()
    {
        return $this->getParameter('trade_no');
    }


    /**
     * @return mixed
     */
    public function getOutTradeNo()
    {
        return $this->getParameter('out_trade_no');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setTradeNo($value)
    {
        return $this->setParameter('trade_no', $value);
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setOutTradeNo($value)
    {
        return $this->setParameter('out_trade_no', $value);
    }
}
