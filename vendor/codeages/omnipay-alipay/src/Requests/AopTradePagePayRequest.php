<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Responses\AopTradePagePayResponse;
/**
 * Class AopTradePagePayRequest
 * @package Omnipay\Alipay\Requests
 * @link    https://doc.open.alipay.com/doc2/detail.htm?treeId=270&articleId=105901&docType=1
 */
class AopTradePagePayRequest extends \Omnipay\Alipay\Requests\AbstractAopRequest
{
    protected $method = 'alipay.trade.page.pay';
    protected $returnable = true;
    protected $notifiable = true;
    public function sendData($data)
    {
        return $this->response = new \Omnipay\Alipay\Responses\AopTradePagePayResponse($this, $data);
    }
    public function validateParams()
    {
        parent::validateParams();
        $this->validateBizContent('subject', 'out_trade_no', 'total_amount', 'product_code');
    }
    protected function getRequestUrl($data)
    {
        $url = sprintf('%s?%s', $this->getEndpoint(), http_build_query($data));
        return $url;
    }
}