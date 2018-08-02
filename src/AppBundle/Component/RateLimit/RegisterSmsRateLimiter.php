<?php

namespace AppBundle\Component\RateLimit;

use Symfony\Component\HttpFoundation\Request;
use Biz\User\UserException;

class RegisterSmsRateLimiter extends SmsRateLimiter
{
    public function handle(Request $request)
    {
        if (!$this->getUserService()->isMobileUnique($request->request->get('mobile'))) {
            throw UserException::ERROR_MOBILE_REGISTERED();
        }

        if ('captchaRequired' == $this->getUserService()->getSmsRegisterCaptchaStatus($request->getClientIp())) {
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

    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
