<?php

namespace Omnipay\PayPal\Support\InstantUpdateApi;

use Omnipay\Common\Exception\InvalidRequestException;

class BillingAgreement
{
    /**
     * Billing agreement types for single or recurring payment
     *
     * @var array
     */
    protected $types = array(
        'single' => 'MerchantInitiatedBillingSingleAgreement',
        'recurring' => 'MerchantInitiatedBilling',
    );

    /** @var string */
    private $type;

    /** @var string */
    private $description;

    /** @var string */
    private $paymentType;

    /** @var string */
    private $customAnnotation;

    /**
     * @param bool $recurring L_BILLINGTYPE0
     * @param string $description L_BILLINGAGREEMENTDESCRIPTION0
     * @param null|string $paymentType L_PAYMENTTYPE0
     * @param null|string $customAnnotation L_BILLINGAGREEMENTCUSTOM0
     * @throws \Exception
     */
    public function __construct($recurring, $description, $paymentType = null, $customAnnotation = null)
    {
        if (!$recurring && !is_null($paymentType) && !in_array($paymentType, array('Any', 'InstantOnly'))) {
            throw new InvalidRequestException("The 'paymentType' parameter can be only 'Any' or 'InstantOnly'");
        }

        $this->type = $recurring ? $this->types['recurring'] : $this->types['single'];
        $this->description = $description;
        $this->customAnnotation = $customAnnotation;
        $this->paymentType = $paymentType;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function hasPaymentType()
    {
        return !is_null($this->paymentType);
    }

    /**
     * @return string
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @return bool
     */
    public function hasCustomAnnotation()
    {
        return !is_null($this->customAnnotation);
    }

    /**
     * @return string
     */
    public function getCustomAnnotation()
    {
        return $this->customAnnotation;
    }
}
