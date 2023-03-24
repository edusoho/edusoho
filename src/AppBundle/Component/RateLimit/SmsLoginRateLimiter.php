<?php

namespace AppBundle\Component\RateLimit;

use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class SmsLoginRateLimiter extends SmsRateLimiter
{
    public function handle(Request $request)
    {
        if ('captchaRequired' == $this->getUserService()->getSmsCommonCaptchaStatus($request->getClientIp())) {
            $this->validateCaptcha($request);
        }

        $ihr = $this->ipHourRateLimiter->check($request->getClientIp());
        $sdr = $this->siteDayRateLimiter->check('site');
        $isLimitReach = $ihr <= 0 || $sdr <= 0;

        if ($isLimitReach) {
            throw $this->createMaxRequestOccurException();
        }
    }

    protected function validateCaptcha($request)
    {
        if ($request->request->has('captchaToken')) {
            $captchaToken = $request->request->get('captchaToken');
            $phrase = $request->request->get('phrase');

            $this->getBizCaptcha()->check($captchaToken, $phrase);
        } else {
            $dragCaptchaToken = $request->request->get('dragCaptchaToken');

            $this->getDragCaptcha()->check($dragCaptchaToken);
        }
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
