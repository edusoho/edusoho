<?php

namespace AppBundle\Component\RateLimit;

use Biz\Common\BizCaptcha;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class AbstractRateLimiter
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function setBiz(Biz $biz)
    {
        $this->biz = $biz;
    }

    protected function createMaxRequestOccurException()
    {
        return new TooManyRequestsHttpException(null, RateLimiterInterface::MAX_REQUEST_MSG_KEY, null, RateLimiterInterface::MAX_REQUEST_OCCUR);
    }

    protected function createCaptchaOccurException()
    {
        return new TooManyRequestsHttpException(null, RateLimiterInterface::CAPTCHA_OCCUR_MSG_KEY, null, RateLimiterInterface::CAPTCHA_OCCUR);
    }

    protected function validateCaptcha($request)
    {
        $token = $request->request->get('captchaToken');
        $phrase = $request->request->get('phrase');

        if (!$token || !$phrase) {
            throw $this->createCaptchaOccurException();
        }

        $status = $this->getBizCaptcha()->check($token, $phrase);

        if (BizCaptcha::STATUS_SUCCESS != $status) {
            throw $this->createCaptchaOccurException();
        }
    }

    /**
     * @return \Biz\Common\BizCaptcha
     */
    private function getBizCaptcha()
    {
        return $this->biz['biz_captcha'];
    }
}
