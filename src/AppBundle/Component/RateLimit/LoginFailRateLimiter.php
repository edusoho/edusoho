<?php

namespace AppBundle\Component\RateLimit;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\RateLimiter\RateLimiter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

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
            throw new BadRequestHttpException();
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
            throw new TooManyRequestsHttpException();
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
