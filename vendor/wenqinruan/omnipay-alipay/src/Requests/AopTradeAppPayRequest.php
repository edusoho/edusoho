<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Responses\AopTradeAppPayResponse;
use Omnipay\Common\Message\ResponseInterface;
/**
 * Class AopTradeAppPayRequest
 * @package Omnipay\Alipay\Requests
 * @link    https://doc.open.alipay.com/docs/doc.htm?treeId=204&articleId=105465&docType=1
 */
class AopTradeAppPayRequest extends \Omnipay\Alipay\Requests\AbstractAopRequest
{
    protected $method = 'alipay.trade.app.pay';
    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return ResponseInterface
     */
    public function sendData($data)
    {
        $data['order_string'] = http_build_query($data);
        return $this->response = new \Omnipay\Alipay\Responses\AopTradeAppPayResponse($this, $data);
    }
    /**
     * @return mixed
     */
    public function getNotifyUrl()
    {
        return $this->getParameter('notify_url');
    }
    /**
     * @param $value
     *
     * @return $this
     */
    public function setNotifyUrl($value)
    {
        return $this->setParameter('notify_url', $value);
    }
}