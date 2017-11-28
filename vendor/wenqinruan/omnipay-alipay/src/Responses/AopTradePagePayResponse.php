<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Alipay\Requests\AopTradeWapPayRequest;
use Omnipay\Common\Message\RedirectResponseInterface;
class AopTradePagePayResponse extends \Omnipay\Alipay\Responses\AbstractResponse implements \Omnipay\Common\Message\RedirectResponseInterface
{
    /**
     * @var AopTradeWapPayRequest
     */
    protected $request;
    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return true;
    }
    public function isRedirect()
    {
        return true;
    }
    /**
     * Gets the redirect target url.
     */
    public function getRedirectUrl()
    {
        return sprintf('%s?%s', $this->request->getEndpoint(), http_build_query($this->data));
    }
    /**
     * Get the required redirect method (either GET or POST).
     */
    public function getRedirectMethod()
    {
        return 'GET';
    }
    /**
     * Gets the redirect form data array, if the redirect method is POST.
     */
    public function getRedirectData()
    {
        return $this->getData();
    }
}