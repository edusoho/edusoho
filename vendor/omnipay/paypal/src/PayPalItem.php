<?php
/**
 * Paypal Item
 */

namespace Omnipay\PayPal;

use Omnipay\Common\Item;

/**
 * Class PayPalItem
 *
 * @package Omnipay\PayPal
 */
class PayPalItem extends Item
{
    /**
     * {@inheritDoc}
     */
    public function getCode()
    {
        return $this->getParameter('code');
    }

    /**
     * Set the item code
     */
    public function setCode($value)
    {
        return $this->setParameter('code', $value);
    }
}
