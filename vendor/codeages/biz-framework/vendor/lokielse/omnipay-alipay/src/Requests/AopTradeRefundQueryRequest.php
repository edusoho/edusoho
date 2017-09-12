<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Responses\AopTradeRefundQueryResponse;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;

/**
 * Class AopTradeRefundQueryRequest
 * @package Omnipay\Alipay\Requests
 * @link    https://doc.open.alipay.com/docs/api.htm?docType=4&apiId=1049
 */
class AopTradeRefundQueryRequest extends AbstractAopRequest
{

    protected $method = 'alipay.trade.fastpay.refund.query';


    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return ResponseInterface
     * @throws InvalidRequestException
     */
    public function sendData($data)
    {
        $data = parent::sendData($data);

        return $this->response = new AopTradeRefundQueryResponse($this, $data);
    }


    public function validateParams()
    {
        parent::validateParams();

        $this->validateBizContent('out_request_no');

        $this->validateBizContentOne(
            'trade_no',
            'out_trade_no'
        );
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
    public function setOutTradeNo($value)
    {
        return $this->setParameter('out_trade_no', $value);
    }


    /**
     * @return mixed
     */
    public function getTradeNo()
    {
        return $this->getParameter('trade_no');
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
}
