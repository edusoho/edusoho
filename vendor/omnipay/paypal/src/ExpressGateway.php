<?php

namespace Omnipay\PayPal;

/**
 * PayPal Express Class
 */
class ExpressGateway extends ProGateway
{
    public function getName()
    {
        return 'PayPal Express';
    }

    public function getDefaultParameters()
    {
        $settings = parent::getDefaultParameters();
        $settings['solutionType'] = array('Sole', 'Mark');
        $settings['landingPage'] = array('Billing', 'Login');
        $settings['brandName'] = '';
        $settings['headerImageUrl'] = '';
        $settings['logoImageUrl'] = '';
        $settings['borderColor'] = '';

        return $settings;
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

    public function getBrandName()
    {
        return $this->getParameter('brandName');
    }

    public function setBrandName($value)
    {
        return $this->setParameter('brandName', $value);
    }

    public function getHeaderImageUrl()
    {
        return $this->getParameter('headerImageUrl');
    }

    public function getLogoImageUrl()
    {
        return $this->getParameter('logoImageUrl');
    }

    public function getBorderColor()
    {
        return $this->getParameter('borderColor');
    }

    /**
     * Header Image URL (Optional)
     *
     * URL for the image you want to appear at the top left of the payment page.
     * The image has a maximum size of 750 pixels wide by 90 pixels high.
     * PayPal recommends that you provide an image that is stored on a secure
     * (HTTPS) server.
     * If you do not specify an image, the business name displays.
     * Character length and limitations: 127 single-byte alphanumeric characters
     */
    public function setHeaderImageUrl($value)
    {
        return $this->setParameter('headerImageUrl', $value);
    }

    /**
     * Logo Image URL (Optional)
     *
     * URL for the image to appear above the order summary, in place of the
     * brand name.
     * The recommended size is 190 pixels wide and 60 pixels high.
     */
    public function setLogoImageUrl($value)
    {
        return $this->setParameter('logoImageUrl', $value);
    }

    /**
     * Border Color (Optional)
     *
     * The color of the border gradient on payment pages.
     * Should be a six character hexadecimal code (i.e. C0C0C0).
     */
    public function setBorderColor($value)
    {
        return $this->setParameter('borderColor', $value);
    }

    public function setSellerPaypalAccountId($value)
    {
        return $this->setParameter('sellerPaypalAccountId', $value);
    }

    public function getSellerPaypalAccountId()
    {
        return $this->getParameter('sellerPaypalAccountId');
    }

    public function authorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PayPal\Message\ExpressAuthorizeRequest', $parameters);
    }

    public function completeAuthorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PayPal\Message\ExpressCompleteAuthorizeRequest', $parameters);
    }

    public function purchase(array $parameters = array())
    {
        return $this->authorize($parameters);
    }

    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PayPal\Message\ExpressCompletePurchaseRequest', $parameters);
    }

    public function void(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PayPal\Message\ExpressVoidRequest', $parameters);
    }

    public function fetchCheckout(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PayPal\Message\ExpressFetchCheckoutRequest', $parameters);
    }

    /**
     * @return Message\ExpressTransactionSearchRequest
     */
    public function transactionSearch(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PayPal\Message\ExpressTransactionSearchRequest', $parameters);
    }

    public function order(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PayPal\Message\ExpressOrderRequest', $parameters);
    }

    public function completeOrder(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PayPal\Message\ExpressCompleteOrderRequest', $parameters);
    }
}
