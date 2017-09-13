<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Responses\VerifyNotifyIdResponse;
use Omnipay\Common\Message\ResponseInterface;
/**
 * https://doc.open.alipay.com/docs/doc.htm?treeId=58&articleId=103597&docType=1
 * Class LegacyVerifyNotifyIdRequest
 * @package Omnipay\Alipay\Requests
 */
class LegacyVerifyNotifyIdRequest extends \Omnipay\Alipay\Requests\AbstractLegacyRequest
{
    protected $service = 'notify_verify';
    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */
    public function getData()
    {
        $this->validate('partner', 'notify_id');
        $data = $this->parameters->all();
        $data['service'] = $this->service;
        return $data;
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
        $url = sprintf('%s?%s', $this->getEndpoint(), http_build_query($data));
        $response = $this->httpClient->get($url)->send()->getBody();
        $data = array('result' => $response);
        return $this->response = new \Omnipay\Alipay\Responses\VerifyNotifyIdResponse($this, $data);
    }
    /**
     * @return mixed
     */
    public function getNotifyId()
    {
        return $this->getParameter('notify_id');
    }
    /**
     * @param $value
     *
     * @return $this
     */
    public function setNotifyId($value)
    {
        return $this->setParameter('notify_id', $value);
    }
}