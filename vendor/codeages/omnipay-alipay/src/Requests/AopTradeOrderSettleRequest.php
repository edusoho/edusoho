<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Responses\AopTradeOrderSettleResponse;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;

/**
 * Class AopTradeOrderSettleRequest
 * @package Omnipay\Alipay\Requests
 * @link    https://doc.open.alipay.com/docs/api.htm?docType=4&apiId=1147
 */
class AopTradeOrderSettleRequest extends AbstractAopRequest
{
    protected $method = 'alipay.trade.order.settle';


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

        return $this->response = new AopTradeOrderSettleResponse($this, $data);
    }


    public function validateParams()
    {
        parent::validateParams();

        $this->validateBizContent(
            'out_request_no',
            'trade_no',
            'royalty_parameters'
        );
        $this->validateBizContentOne(
            'out_trade_no',
            'trade_no'
        );
    }
}
