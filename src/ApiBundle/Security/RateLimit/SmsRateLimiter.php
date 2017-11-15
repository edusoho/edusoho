<?php

namespace ApiBundle\Security\RateLimit;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\RateLimiter\RateLimiter;
use Symfony\Component\HttpFoundation\Request;

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

    const IP_MAX_ALLOW_ATTEMPT_ONE_HOUR = 1000;

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
            return array(
                'code' => RateLimiterInterface::PASS,
                'message' => 'ok'
            );
        }

        $needCaptcha = $icr == 0 && $ihr > 0 && $sdr > 0;

        if ($needCaptcha) {
            return array(
                'code' => RateLimiterInterface::CAPTCHA_OCCUR,
                'message' => 'request.need.captcha',
            );
        } else {
            return array(
                'code' => RateLimiterInterface::MAX_REQUEST_OCCUR,
                'message' => 'request.max_attempt_reach'
            );
        }

    }

    public function setRateLimiter(RateLimiter $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }
}