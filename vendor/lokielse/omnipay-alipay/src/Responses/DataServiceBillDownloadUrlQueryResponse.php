<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Alipay\Requests\DataServiceBillDownloadUrlQueryRequest;
class DataServiceBillDownloadUrlQueryResponse extends \Omnipay\Alipay\Responses\AbstractAopResponse
{
    protected $key = 'alipay_data_dataservice_bill_downloadurl_query_response';
    /**
     * @var DataServiceBillDownloadUrlQueryRequest
     */
    protected $request;
}