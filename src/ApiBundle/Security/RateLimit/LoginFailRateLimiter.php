<?php

namespace ApiBundle\Security\RateLimit;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\RateLimiter\RateLimiter;
use Symfony\Component\HttpFoundation\Request;

class LoginFailRateLimiter implements RateLimiterInterface
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
        $username = $request->request->get('username');
        $password = $request->request->get('password');

    }

    public function setRateLimiter(RateLimiter $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }
}