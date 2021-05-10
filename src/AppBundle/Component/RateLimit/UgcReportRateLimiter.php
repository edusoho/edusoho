<?php

namespace AppBundle\Component\RateLimit;

use AppBundle\Common\TimeMachine;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\HttpFoundation\Request;

class UgcReportRateLimiter extends AbstractRateLimiter implements RateLimiterInterface
{
    /**
     * @var \Codeages\RateLimiter\RateLimiter
     */
    protected $userDayRateLimiter;

    const USER_MAX_ALLOW_ATTEMPT_ONE_DAY = 3;

    public function __construct(Biz $biz)
    {
        parent::__construct($biz);

        $factory = $biz['ratelimiter.factory'];
        $this->userDayRateLimiter = $factory('ugc.user.report.one_day', self::USER_MAX_ALLOW_ATTEMPT_ONE_DAY, TimeMachine::ONE_DAY);
    }

    public function handle(Request $request)
    {
        $user = $this->biz['user'];
        $udr = $this->userDayRateLimiter->check($user['id']);

        return $udr;
    }

    public function getAllow()
    {
        $user = $this->biz['user'];
        $allow = $this->userDayRateLimiter->getAllow($user['id']);
        if (!$allow) {
            throw $this->createUgcReportMaxRequestOccurException();
        }
    }
}
