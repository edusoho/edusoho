<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Alipay\Requests\AopTransferToAccountRequest;

class AopTransferToAccountResponse extends AbstractAopResponse
{
    protected $key = 'alipay_fund_trans_toaccount_transfer_response';

    /**
     * @var AopTransferToAccountRequest
     */
    protected $request;
}
