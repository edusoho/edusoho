<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Responses\LegacyRefundNoPwdResponse;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;

/**
 * Class LegacyRefundRequest
 * @package Omnipay\Alipay\Requests
 * @link    https://doc.open.alipay.com/docs/doc.htm?treeId=66&articleId=103600&docType=1
 */
class LegacyRefundNoPwdRequest extends AbstractLegacyRequest
{
    protected $service = 'refund_fastpay_by_platform_nopwd';


    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */
    public function getData()
    {
        $this->setDefaults();

        $this->validate(
            'partner',
            '_input_charset',
            'refund_date',
            'batch_no',
            'refund_items'
        );

        $this->validateOne(
            'seller_user_id',
            'seller_email'
        );

        $this->setBatchNum(count($this->getRefundItems()));
        $this->setRefundDetail($this->getDetailData());

        $data = [
            'service'        => $this->service,
            'partner'        => $this->getPartner(),
            'notify_url'     => $this->getNotifyUrl(),
            'seller_user_id' => $this->getPartner(),
            'refund_date'    => $this->getRefundDate(),
            'batch_no'       => $this->getBatchNo(),
            'batch_num'      => $this->getBatchNum(),
            'detail_data'    => $this->getDetailData(),
            '_input_charset' => $this->getInputCharset(),
        ];

        $data['sign'] = $this->sign($data, $this->getSignType());
        $data['sign_type'] = $this->getSignType();

        return $data;
    }


    protected function setDefaults()
    {
        if (!$this->getRefundDate()) {
            $this->setRefundDate(date('Y-m-d H:i:s'));
        }

        if (!$this->getBatchNo()) {
            $this->setBatchNo(date('Ymd') . mt_rand(1000, 9999));
        }
    }


    /**
     * @return mixed
     */
    public function getRefundDate()
    {
        return $this->getParameter('refund_date');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setRefundDate($value)
    {
        return $this->setParameter('refund_date', $value);
    }


    /**
     * @return mixed
     */
    public function getBatchNo()
    {
        return $this->getParameter('batch_no');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setBatchNo($value)
    {
        return $this->setParameter('batch_no', $value);
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setBatchNum($value)
    {
        return $this->setParameter('batch_num', $value);
    }


    /**
     * @return mixed
     */
    public function getRefundItems()
    {
        return $this->getParameter('refund_items');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    protected function setRefundDetail($value)
    {
        return $this->setParameter('refund_detail', $value);
    }


    protected function getDetailData()
    {
        $strings = [];

        foreach ($this->getRefundItems() as $item) {
            $item = (array)$item;

            if (!isset($item['trade_no'])) {
                throw new InvalidRequestException('The field `trade_no` is not exist in item');
            }

            if (!isset($item['amount'])) {
                throw new InvalidRequestException('The field `amount` is not exist in item');
            }

            if (!isset($item['reason'])) {
                throw new InvalidRequestException('The field `reason` is not exist in item');
            }

            $strings[] = implode('^', [ $item['trade_no'], $item['amount'], $item['reason'] ]);
        }

        return implode('#', $strings);
    }


    /**
     * @return mixed
     */
    public function getPartner()
    {
        return $this->getParameter('partner');
    }


    /**
     * @return mixed
     */
    public function getNotifyUrl()
    {
        return $this->getParameter('notify_url');
    }


    /**
     * @return mixed
     */
    public function getBatchNum()
    {
        return $this->getParameter('batch_num');
    }


    /**
     * @return mixed
     */
    public function getInputCharset()
    {
        return $this->getParameter('_input_charset');
    }


    /**
     * @return mixed
     */
    public function getPaymentType()
    {
        return $this->getParameter('payment_type');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setPaymentType($value)
    {
        return $this->setParameter('payment_type', $value);
    }


    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return ResponseInterface
     */
    public function sendData($data)
    {
        $url = sprintf('%s?%s', $this->getEndpoint(), http_build_query($this->getData()));

        $result = $this->httpClient->request('get', $url)->getBody();

        $xml = simplexml_load_string($result);
        $json = json_encode($xml);
        $data = json_decode($json, true);

        return $this->response = new LegacyRefundNoPwdResponse($this, $data);
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setPartner($value)
    {
        return $this->setParameter('partner', $value);
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setInputCharset($value)
    {
        return $this->setParameter('_input_charset', $value);
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


    /**
     * @return mixed
     */
    public function getSellerEmail()
    {
        return $this->getParameter('seller_email');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setSellerEmail($value)
    {
        return $this->setParameter('seller_email', $value);
    }


    /**
     * @return mixed
     */
    public function getSellerId()
    {
        return $this->getSellerUserId();
    }


    /**
     * @return mixed
     */
    public function getSellerUserId()
    {
        return $this->getParameter('seller_user_id');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setSellerId($value)
    {
        return $this->setSellerUserId($value);
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setSellerUserId($value)
    {
        return $this->setParameter('seller_user_id', $value);
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setRefundItems($value)
    {
        return $this->setParameter('refund_items', $value);
    }


    /**
     * @return mixed
     */
    protected function getRefundDetail()
    {
        return $this->getParameter('refund_detail');
    }
}
