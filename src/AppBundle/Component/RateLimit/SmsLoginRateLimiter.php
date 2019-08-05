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
        $token = $request->request->get('dragCaptchaToken');
        $this->getDragCaptcha()->check($token);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
