<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Responses\AopTradePreCreateResponse;
use Omnipay\Common\Message\ResponseInterface;

/**
 * Class AopTradePreCreateRequest
 * @package Omnipay\Alipay\Requests
 * @link    https://doc.open.alipay.com/docs/api.htm?docType=4&apiId=862
 */
class AopTradePreCreateRequest extends AbstractAopRequest
{
    protected $method = 'alipay.trade.precreate';


    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return ResponseInterface
     * @throws \Psr\Http\Client\Exception\NetworkException
     * @throws \Psr\Http\Client\Exception\RequestException
     */
    public function sendData($data)
    {
        $data = parent::sendData($data);

        return $this->response = new AopTradePreCreateResponse($this, $data);
    }


    /**
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function validateParams()
    {
        parent::validateParams();

        $this->validateBizContent(
            'out_trade_no',
            'total_amount',
            'subject'
        );
    }
}
