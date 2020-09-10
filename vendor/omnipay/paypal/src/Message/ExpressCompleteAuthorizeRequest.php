<?php

namespace Omnipay\PayPal\Message;

/**
 * PayPal Express Complete Authorize Request
 */
class ExpressCompleteAuthorizeRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('amount');

        $data = $this->getBaseData();
        $data['METHOD'] = 'DoExpressCheckoutPayment';
        $data['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Authorization';
        $data['PAYMENTREQUEST_0_AMT'] = $this->getAmount();
        $data['PAYMENTREQUEST_0_CURRENCYCODE'] = $this->getCurrency();
        $data['PAYMENTREQUEST_0_INVNUM'] = $this->getTransactionId();
        $data['PAYMENTREQUEST_0_DESC'] = $this->getDescription();
        $data['PAYMENTREQUEST_0_NOTIFYURL'] = $this->getNotifyUrl();

        $data['MAXAMT'] = $this->getMaxAmount();
        $data['PAYMENTREQUEST_0_TAXAMT'] = $this->getTaxAmount();
        $data['PAYMENTREQUEST_0_SHIPPINGAMT'] = $this->getShippingAmount();
        $data['PAYMENTREQUEST_0_HANDLINGAMT'] = $this->getHandlingAmount();
        $data['PAYMENTREQUEST_0_SHIPDISCAMT'] = $this->getShippingDiscount();
        $data['PAYMENTREQUEST_0_INSURANCEAMT'] = $this->getInsuranceAmount();

        $data['TOKEN'] = $this->getToken() ? $this->getToken() : $this->httpRequest->query->get('token');
        $data['PAYERID'] = $this->getPayerID() ? $this->getPayerID() : $this->httpRequest->query->get('PayerID');

        $data = array_merge($data, $this->getItemData());

        return $data;
    }

    public function getPayerID()
    {
        return $this->getParameter('payerID');
    }

    public function setPayerID($value)
    {
        return $this->setParameter('payerID', $value);
    }
}
