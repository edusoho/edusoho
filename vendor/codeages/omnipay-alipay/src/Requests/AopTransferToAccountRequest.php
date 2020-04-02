<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Responses\AopTransferToAccountResponse;
use Omnipay\Common\Message\ResponseInterface;

/**
 * Class AopTransferToAccountRequest
 *
 * @package Omnipay\Alipay\Requests
 * @link    https://docs.open.alipay.com/api_28/alipay.fund.trans.toaccount.transfer
 */
class AopTransferToAccountRequest extends AbstractAopRequest
{
    protected $method = 'alipay.fund.trans.toaccount.transfer';


    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return ResponseInterface
     */
    public function sendData($data)
    {
        $data = parent::sendData($data);

        return $this->response = new AopTransferToAccountResponse($this, $data);
    }


    public function validateParams()
    {
        parent::validateParams();

        $this->validateBizContent(
            'out_biz_no',
            'payee_type',
            'payee_account',
            'amount'
        );
    }
}
