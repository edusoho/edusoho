<?php

namespace AppBundle\Component\RateLimit;

use AppBundle\Common\TimeMachine;
use Codeages\RateLimiter\RateLimiter;
use AppBundle\Controller\OAuth2\OAuthUser;
use Symfony\Component\HttpFoundation\Request;

class RegisterRateLimiter extends AbstractRateLimiter implements RateLimiterInterface
{
    const MID_IP_MAX_ALLOW_ATTEMPT_ONE_DAY = 30;

    const HIGH_IP_MAX_ALLOW_ATTEMPT_ONE_DAY = 10;

    const HIGH_IP_MAX_ALLOW_ATTEMPT_ONE_HOUR = 1;

    public function handle(Request $request)
    {
        switch ($this->getRegisterProtective()) {
            case 'none':
                return;
            case 'low':
                $oauthUser = $this->getOauthUser($request);
                if ($oauthUser->captchaEnabled) {
                    $this->validateCaptcha($request);
                }
                break;
            case 'middle':
                $oauthUser = $this->getOauthUser($request);
                if ($oauthUser->captchaEnabled) {
                    $this->validateCaptcha($request);
                }
                $factory = $this->biz['ratelimiter.factory'];
                /** @var RateLimiter $limiter */
                $limiter = $factory('register.ip.mid_one_day', self::MID_IP_MAX_ALLOW_ATTEMPT_ONE_DAY, TimeMachine::ONE_DAY);

                $remain = $limiter->check($request->getClientIp());

                if (0 == $remain) {
                    throw $this->createMaxRequestOccurException();
                }

                break;
            case 'high':
                //手机不需要校验验证码，只需要短信校验码
                $oauthUser = $this->getOauthUser($request);
                if ($oauthUser->captchaEnabled) {
                    $this->validateCaptcha($request);
                }

                $factory = $this->biz['ratelimiter.factory'];
                /** @var RateLimiter $dayLimiter */
                $dayLimiter = $factory('register.ip.high_one_day', self::HIGH_IP_MAX_ALLOW_ATTEMPT_ONE_DAY, TimeMachine::ONE_DAY);
                $remain = $dayLimiter->check($request->getClientIp());
                if (0 == $remain) {
                    throw $this->createMaxRequestOccurException();
                }

                /** @var RateLimiter $hourLimiter */
                $hourLimiter = $factory('register.ip.high_one_hour', self::HIGH_IP_MAX_ALLOW_ATTEMPT_ONE_HOUR, TimeMachine::ONE_HOUR);
                $remain = $hourLimiter->check($request->getClientIp());
                if (0 == $remain) {
                    throw $this->createMaxRequestOccurException();
                }

                break;
            default:
                return;
        }
    }

    protected function validateCaptcha($request)
    {
        $token = $request->request->get('dragCaptchaToken', '');
        $this->getDragCaptcha()->check($token);
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

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \AppBundle\Controller\OAuth2\OAuthUser
     */
    private function getOauthUser(Request $request)
    {
        $oauthUser = $request->getSession()->get(OAuthUser::SESSION_KEY);
        if (!$oauthUser) {
            throw new NotFoundHttpException();
        }

        return $oauthUser;
    }
}
