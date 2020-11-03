<?php

namespace Omnipay\PayPal\Support\InstantUpdateApi;

class ShippingOption
{
    /** @var string */
    private $name;

    /** @var float */
    private $amount;

    /** @var bool */
    private $isDefault;

    /** @var string */
    private $label;

    /**
     * @param string $name      L_SHIPPINGOPTIONNAME0
     * @param float  $amount    L_SHIPPINGOPTIONAMOUNT0
     * @param bool   $isDefault L_SHIPPINGOPTIONISDEFAULT0
     * @param string $label     L_SHIPPINGOPTIONLABEL0
     */
    public function __construct($name, $amount, $isDefault = false, $label = null)
    {
        $this->name      = $name;
        $this->amount    = $amount;
        $this->isDefault = $isDefault;
        $this->label     = $label;
    }

    /**
     * @return bool
     */
    public function hasLabel()
    {
        return !is_null($this->label);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return boolean
     */
    public function isDefault()
    {
        return $this->isDefault;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
}
