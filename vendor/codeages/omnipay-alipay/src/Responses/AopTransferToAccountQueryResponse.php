<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Alipay\Requests\AopTransferToAccountQueryRequest;

class AopTransferToAccountQueryResponse extends AbstractAopResponse
{
    protected $key = 'alipay_fund_trans_order_query_response';

    /**
     * @var AopTransferToAccountQueryRequest
     */
    protected $request;
}
