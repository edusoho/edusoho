<?php
/**
 * PayPal Abstract Request
 */

namespace Omnipay\PayPal\Message;

use Omnipay\Common\ItemBag;
use Omnipay\PayPal\PayPalItem;
use Omnipay\PayPal\PayPalItemBag;

/**
 * PayPal Abstract Request
 *
 * This class forms the base class for PayPal Express Checkout and Pro Checkout
 * requests.  These are also known as "Payflow Gateway" requests and also
 * "PayPal Classic APIs".
 *
 * According to the PayPal documentation:
 *
 * * This is the recommended way to integrate when you want to accept payments
 *   with a completely customizable solution. This integration method leverages
 *   the PayPal Payflow Gateway to transmit payments your PayPal Internet Merchant
 *   Account; it also gives the merchant the flexibility to change payment
 *   processors without having to re-do their technical integration. When using
 *   PayPal Payments Pro (Payflow Edition) using Payflow Gateway integration,
 *   merchants can use Transparent Redirect feature to help manage PCI compliance.
 *
 * @link https://developer.paypal.com/docs/classic/products/payflow-gateway/
 * @link https://developer.paypal.com/docs/classic/express-checkout/gs_expresscheckout/
 * @link https://developer.paypal.com/docs/classic/products/ppp-payflow-edition/
 * @link https://devtools-paypal.com/integrationwizard/
 * @link http://paypal.github.io/sdk/
 */
abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    const API_VERSION = '119.0';

    protected $liveEndpoint = 'https://api-3t.paypal.com/nvp';
    protected $testEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
    
    /**
     * @var bool
     */
    protected $negativeAmountAllowed = true;

    public function getUsername()
    {
        return $this->getParameter('username');
    }

    public function setUsername($value)
    {
        return $this->setParameter('username', $value);
    }

    public function getPassword()
    {
        return $this->getParameter('password');
    }

    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    public function getSignature()
    {
        return $this->getParameter('signature');
    }

    public function setSignature($value)
    {
        return $this->setParameter('signature', $value);
    }

    public function getSubject()
    {
        return $this->getParameter('subject');
    }

    public function setSubject($value)
    {
        return $this->setParameter('subject', $value);
    }

    public function getSolutionType()
    {
        return $this->getParameter('solutionType');
    }

    public function setSolutionType($value)
    {
        return $this->setParameter('solutionType', $value);
    }

    public function getLandingPage()
    {
        return $this->getParameter('landingPage');
    }

    public function setLandingPage($value)
    {
        return $this->setParameter('landingPage', $value);
    }

    public function getHeaderImageUrl()
    {
        return $this->getParameter('headerImageUrl');
    }

    public function setHeaderImageUrl($value)
    {
        return $this->setParameter('headerImageUrl', $value);
    }

    public function getLogoImageUrl()
    {
        return $this->getParameter('logoImageUrl');
    }

    public function setLogoImageUrl($value)
    {
        return $this->setParameter('logoImageUrl', $value);
    }

    public function getBorderColor()
    {
        return $this->getParameter('borderColor');
    }

    public function setBorderColor($value)
    {
        return $this->setParameter('borderColor', $value);
    }

    public function getBrandName()
    {
        return $this->getParameter('brandName');
    }

    public function setBrandName($value)
    {
        return $this->setParameter('brandName', $value);
    }

    public function getNoShipping()
    {
        return $this->getParameter('noShipping');
    }

    public function setNoShipping($value)
    {
        return $this->setParameter('noShipping', $value);
    }

    public function getAllowNote()
    {
        return $this->getParameter('allowNote');
    }

    public function setAllowNote($value)
    {
        return $this->setParameter('allowNote', $value);
    }

    public function getAddressOverride()
    {
        return $this->getParameter('addressOverride');
    }

    public function setAddressOverride($value)
    {
        return $this->setParameter('addressOverride', $value);
    }

    public function getMaxAmount()
    {
        return $this->getParameter('maxAmount');
    }

    public function setMaxAmount($value)
    {
        return $this->setParameter('maxAmount', $value);
    }

    public function getTaxAmount()
    {
        return $this->getParameter('taxAmount');
    }

    public function setTaxAmount($value)
    {
        return $this->setParameter('taxAmount', $value);
    }

    public function getShippingAmount()
    {
        return $this->getParameter('shippingAmount');
    }

    public function setShippingAmount($value)
    {
        return $this->setParameter('shippingAmount', $value);
    }

    public function getHandlingAmount()
    {
        return $this->getParameter('handlingAmount');
    }

    public function setHandlingAmount($value)
    {
        return $this->setParameter('handlingAmount', $value);
    }

    public function getShippingDiscount()
    {
        return $this->getParameter('shippingDiscount');
    }

    public function setShippingDiscount($value)
    {
        return $this->setParameter('shippingDiscount', $value);
    }

    public function getInsuranceAmount()
    {
        return $this->getParameter('insuranceAmount');
    }

    public function setInsuranceAmount($value)
    {
        return $this->setParameter('insuranceAmount', $value);
    }

    public function getLocaleCode()
    {
        return $this->getParameter('localeCode');
    }

    /*
     * Used to change the locale of PayPal pages.
     * Accepts 2 or 5 character language codes as described here:
     * https://developer.paypal.com/docs/classic/express-checkout/integration-guide/ECCustomizing/
     *
     * If no value/invalid value is passed, the gateway will default it for you
    */
    public function setLocaleCode($value)
    {
        return $this->setParameter('localeCode', $value);
    }

    public function setCustomerServiceNumber($value)
    {
        return $this->setParameter('customerServiceNumber', $value);
    }

    public function getCustomerServiceNumber()
    {
        return $this->getParameter('customerServiceNumber');
    }

    public function setSellerPaypalAccountId($value)
    {
        return $this->setParameter('sellerPaypalAccountId', $value);
    }

    public function getSellerPaypalAccountId()
    {
        return $this->getParameter('sellerPaypalAccountId');
    }

    /**
     * The Button Source (BN Code) is for PayPal Partners taking payments for a 3rd party
     */
    public function setButtonSource($value)
    {
        return $this->setParameter('ButtonSource', $value);
    }

    public function getButtonSource()
    {
        return $this->getParameter('ButtonSource');
    }

    protected function getBaseData()
    {
        $data = array();
        $data['VERSION'] = static::API_VERSION;
        $data['USER'] = $this->getUsername();
        $data['PWD'] = $this->getPassword();
        $data['SIGNATURE'] = $this->getSignature();
        $data['SUBJECT'] = $this->getSubject();
        $bnCode = $this->getButtonSource();
        if (!empty($bnCode)) {
            $data['BUTTONSOURCE'] = $bnCode;
        }
        
        return $data;
    }

    protected function getItemData()
    {
        $data = array();
        $items = $this->getItems();
        if ($items) {
            $data["PAYMENTREQUEST_0_ITEMAMT"] = 0;
            foreach ($items as $n => $item) {
                $data["L_PAYMENTREQUEST_0_NAME$n"] = $item->getName();
                $data["L_PAYMENTREQUEST_0_DESC$n"] = $item->getDescription();
                $data["L_PAYMENTREQUEST_0_QTY$n"] = $item->getQuantity();
                $data["L_PAYMENTREQUEST_0_AMT$n"] = $this->formatCurrency($item->getPrice());
                if ($item instanceof PayPalItem) {
                    $data["L_PAYMENTREQUEST_0_NUMBER$n"] = $item->getCode();
                }

                $data["PAYMENTREQUEST_0_ITEMAMT"] += $item->getQuantity() * $this->formatCurrency($item->getPrice());
            }
            $data["PAYMENTREQUEST_0_ITEMAMT"] = $this->formatCurrency($data["PAYMENTREQUEST_0_ITEMAMT"]);
        }

        return $data;
    }

    public function sendData($data)
    {
        $httpResponse = $this->httpClient->request('POST', $this->getEndpoint(), [], http_build_query($data, '', '&'));

        return $this->createResponse($httpResponse->getBody()->getContents());
    }

    protected function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }

    protected function createResponse($data)
    {
        return $this->response = new Response($this, $data);
    }

    /**
     * Set the items in this order
     *
     * @param ItemBag|array $items An array of items in this order
     */
    public function setItems($items)
    {
        if ($items && !$items instanceof ItemBag) {
            $items = new PayPalItemBag($items);
        }

        return $this->setParameter('items', $items);
    }
}
