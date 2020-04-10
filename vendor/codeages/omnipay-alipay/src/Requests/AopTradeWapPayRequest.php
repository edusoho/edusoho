<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Responses\AopTradeWapPayResponse;

/**
 * Class AopTradeWapPayRequest
 * @package Omnipay\Alipay\Requests
 * @link    https://doc.open.alipay.com/doc2/detail.htm?treeId=203&articleId=105463&docType=1
 */
class AopTradeWapPayRequest extends AbstractAopRequest
{
    protected $method = 'alipay.trade.wap.pay';

    protected $returnable = true;

    protected $notifiable = true;


    public function sendData($data)
    {
        return $this->response = new AopTradeWapPayResponse($this, $data);
    }


    public function validateParams()
    {
        parent::validateParams();

        $this->validateBizContent(
            'subject',
            'out_trade_no',
            'total_amount',
            'product_code'
        );
    }


    protected function getRequestUrl($data)
    {
        $url = sprintf('%s?%s', $this->getEndpoint(), http_build_query($data));

        return $url;
    }
}
