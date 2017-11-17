<?php

namespace AppBundle\Component\RateLimit;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\RateLimiter\RateLimiter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class SmsRateLimiter implements RateLimiterInterface
{
    /**
     * @var \Codeages\RateLimiter\RateLimiter
     */
    private $ipCaptchaRateLimiter;

    /**
     * @var \Codeages\RateLimiter\RateLimiter
     */
    private $ipHourRateLimiter;

    /**
     * @var \Codeages\RateLimiter\RateLimiter
     */
    private $siteDayRateLimiter;

    const IP_CAPTCHA_MAX_ALLOW_ATTEMPT_ONE_HOUR = 3;

    const IP_MAX_ALLOW_ATTEMPT_ONE_HOUR = 10;

    const SITE_MAX_ALLOW_ATTEMPT_ONE_DAY = 100000;

    public function __construct(Biz $biz)
    {
        $factory = $biz['ratelimiter.factory'];

        $this->ipCaptchaRateLimiter = $factory('sms.ip.captcha', self::IP_CAPTCHA_MAX_ALLOW_ATTEMPT_ONE_HOUR, 60 * 60);

        $this->ipHourRateLimiter = $factory('sms.ip.max_allow_attempt_period_hour', self::IP_MAX_ALLOW_ATTEMPT_ONE_HOUR, 60 * 60);

        $this->siteDayRateLimiter = $factory('sms.site.max_allow_attempt_period_day', self::SITE_MAX_ALLOW_ATTEMPT_ONE_DAY, 60 * 60 * 24);
    }

    public function handle(Request $request)
    {
        $icr = $this->ipCaptchaRateLimiter->check($request->getClientIp());
        $ihr = $this->ipHourRateLimiter->check($request->getClientIp());
        $sdr = $this->siteDayRateLimiter->check('site');

        $ok = $icr > 0 && $ihr > 0 && $sdr > 0;

        if ($ok) {
            return;
        }

        $needCaptcha = $icr == 0 && $ihr > 0 && $sdr > 0;

        if ($needCaptcha) {
            throw new TooManyRequestsHttpException(null, 'request.need_verify_captcha', null, RateLimiterInterface::CAPTCHA_OCCUR);
        } else {
            throw new TooManyRequestsHttpException(null, 'request.max_attempt_reach', null, RateLimiterInterface::MAX_REQUEST_OCCUR);
        }
    }

    public function setRateLimiter(RateLimiter $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }
}
