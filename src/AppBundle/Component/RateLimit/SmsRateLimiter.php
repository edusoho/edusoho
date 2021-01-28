<?php

namespace AppBundle\Component\RateLimit;

use AppBundle\Common\TimeMachine;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\HttpFoundation\Request;

class SmsRateLimiter extends AbstractRateLimiter implements RateLimiterInterface
{
    /**
     * @var \Codeages\RateLimiter\RateLimiter
     */
    protected $ipHourRateLimiter;

    /**
     * @var \Codeages\RateLimiter\RateLimiter
     */
    protected $siteDayRateLimiter;

    const IP_MAX_ALLOW_ATTEMPT_ONE_HOUR = 15;

    const SITE_MAX_ALLOW_ATTEMPT_ONE_DAY = 100000;

    public function __construct(Biz $biz)
    {
        parent::__construct($biz);

        $factory = $biz['ratelimiter.factory'];
        $this->ipHourRateLimiter = $factory('sms.ip.one_hour', self::IP_MAX_ALLOW_ATTEMPT_ONE_HOUR, TimeMachine::ONE_HOUR);
        $this->siteDayRateLimiter = $factory('sms.site.one_day', self::SITE_MAX_ALLOW_ATTEMPT_ONE_DAY, TimeMachine::ONE_DAY);
    }

    public function handle(Request $request)
    {
        $ihr = $this->ipHourRateLimiter->check($request->getClientIp());
        $sdr = $this->siteDayRateLimiter->check('site');

        $isLimitReach = $ihr <= 0 || $sdr <= 0;
        if ($isLimitReach) {
            throw $this->createMaxRequestOccurException();
        }
    }
}
