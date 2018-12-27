<?php

namespace AppBundle\Component\RateLimit;

use Biz\Common\CommonException;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\RateLimiter\RateLimiter;
use Symfony\Component\HttpFoundation\Request;

class LoginFailRateLimiter implements RateLimiterInterface
{
    /**
     * @var Biz
     */
    private $biz;

    /**
     * @var \Codeages\RateLimiter\RateLimiter
     */
    private $loginFailRateLimiter;

    const MAX_LOGIN_FAIL_ATTEMPT_ONE_HOUR = 120;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
        $factory = $biz['ratelimiter.factory'];

        $this->loginFailRateLimiter = $factory('login_fail_limiter', self::MAX_LOGIN_FAIL_ATTEMPT_ONE_HOUR, 60 * 60);
    }

    public function handle(Request $request)
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        if (!$username || !$password) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $user = $this->getUserService()->getUserByLoginField($username);

        if (!$user) {
            $remaind = $this->loginFailRateLimiter->check($request->getClientIp());
        } else {
            $isFail = !$this->getUserService()->verifyPassword($user['id'], $password);

            if ($isFail) {
                $remaind = $this->loginFailRateLimiter->check($username);
            } else {
                $remaind = 1;
            }
        }

        if ($remaind <= 0) {
            throw CommonException::FORBIDDEN_FREQUENT_OPERATION();
        }
    }

    public function setRateLimiter(RateLimiter $rateLimiter)
    {
        $this->loginFailRateLimiter = $rateLimiter;
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    private function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
