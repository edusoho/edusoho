<?php

namespace AppBundle\Handler;

use Biz\Role\Util\PermissionBuilder;
use Biz\System\Service\SettingService;
use Biz\User\Service\TokenService;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Topxia\MobileBundleV2\Controller\MobileBaseController;
use Topxia\Service\Common\ServiceKernel;

/**
 * Custom login listener.
 */
class LoginSuccessHandler
{
    /**
     * @var AuthorizationChecker
     */
    private $checker;

    /**
     * Constructor.
     *
     * @param AuthorizationChecker $checker
     */
    public function __construct(AuthorizationChecker $checker)
    {
        $this->checker = $checker;
    }

    /**
     * Do the magic.
     *
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        if ($this->checker->isGranted('IS_AUTHENTICATED_FULLY')) {
            // user has just logged in
        }

        if ($this->checker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            // user has logged in using remember_me cookie
        }

        // do some other magic here
        $user = $event->getAuthenticationToken()->getUser();
        $user->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($user->getRoles()));

        $request = $event->getRequest();
        $sessionId = $request->getSession()->getId();
        $request->getSession()->set('loginIp', $request->getClientIp());

        $this->getUserService()->markLoginInfo();
        $this->getUserService()->rememberLoginSessionId($user['id'], $sessionId);

        $this->destroyAppLoginToken($user['id']);
    }

    protected function destroyAppLoginToken($userId)
    {
        $loginBind = $this->getSettingService()->get('login_bind');
        if (empty($loginBind['client_login_limit'])) {
            return;
        }

        $tokens = $this->getTokenService()->findTokensByUserIdAndType($userId, MobileBaseController::TOKEN_TYPE);
        foreach ($tokens as $token) {
            if (!isset($token['data']['client']) || 'app' == $token['data']['client']) {
                $this->getTokenService()->destoryToken($token['token']);
            }
        }
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return ServiceKernel::instance()->createService('User:TokenService');
    }

    private function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getLogService()
    {
        return ServiceKernel::instance()->createService('System:LogService');
    }
}
