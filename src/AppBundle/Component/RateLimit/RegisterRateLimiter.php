<?php

namespace AppBundle\Component\RateLimit;

use AppBundle\Common\TimeMachine;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\RateLimiter\RateLimiter;
use Symfony\Component\HttpFoundation\Request;

class RegisterRateLimiter extends AbstractRateLimiter implements RateLimiterInterface
{
    /**
     * @var Biz
     */
    private $biz;

    const MID_IP_MAX_ALLOW_ATTEMPT_ONE_DAY = 30;

    const HIGH_IP_MAX_ALLOW_ATTEMPT_ONE_DAY = 10;

    const HIGH_IP_MAX_ALLOW_ATTEMPT_ONE_HOUR = 1;

    public function handle(Request $request)
    {
        switch ($this->getRegisterProtective()) {
            case 'none':
                return;
            case 'low':
                $this->validateCaptcha($request);
                break;
            case 'middle':
                $this->validateCaptcha($request);
                $factory = $this->biz['ratelimiter.factory'];
                /** @var RateLimiter $limiter */
                $limiter = $factory('register.ip.mid_max_allow_attempt_period_day', self::MID_IP_MAX_ALLOW_ATTEMPT_ONE_DAY, TimeMachine::ONE_DAY);

                $remain = $limiter->check($request->getClientIp());

                if ($remain == 0) {
                    throw $this->createMaxRequestOccurException();
                }

                break;
            case 'high':
                $this->validateCaptcha($request);

                $factory = $this->biz['ratelimiter.factory'];
                /** @var RateLimiter $dayLimiter */
                $dayLimiter = $factory('register.ip.high_max_allow_attempt_period_day', self::HIGH_IP_MAX_ALLOW_ATTEMPT_ONE_DAY, TimeMachine::ONE_DAY);
                $remain = $dayLimiter->check($request->getClientIp());
                if ($remain == 0) {
                    throw $this->createMaxRequestOccurException();
                }

                /** @var RateLimiter $hourLimiter */
                $hourLimiter = $factory('register.ip.high_max_allow_attempt_period_hour', self::HIGH_IP_MAX_ALLOW_ATTEMPT_ONE_HOUR, TimeMachine::ONE_DAY);
                $remain = $hourLimiter->check($request->getClientIp());
                if ($remain == 0) {
                    throw $this->createMaxRequestOccurException();
                }

                break;
            default:
                return;
        }
    }

    private function getRegisterProtective()
    {
        $registerSetting = $this->getSettingService()->get('auth');

        return empty($registerSetting['register_protective']) ? 'none' : $registerSetting['register_protective'];
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
