<?php

namespace AppBundle\Listener;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Biz\User\UserException;

class UserLoginTokenListener
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onGetUserLoginListener(GetResponseEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();
        if (empty($session)) {
            return;
        }

        $user = $this->getUser();
        if (!$user->islogin()) {
            return;
        }
        if (isset($user['locked']) && $user['locked'] == 1) {
            $this->container->get('security.token_storage')->setToken(null);
            setcookie('REMEMBERME');

            return;
        }

        $auth = $this->getSettingService()->get('auth');
        $route = $request->get('_route');

        if ($auth
            && $auth['register_mode'] != 'mobile'
            && array_key_exists('email_enabled', $auth)
            && $user['createdTime'] > $auth['setting_time']
            && $user['emailVerified'] == 0
            && ($user['type'] == 'default' || $user['type'] == 'web_email' || $user['type'] == 'web_mobile' || $user['type'] == 'discuz' || $user['type'] == 'phpwind' || $user['type'] == 'import')
            && ($auth['email_enabled'] == 'opened' && empty($user['verifiedMobile']))
            && (isset($route))
            && ($route != '')
            && ($route != 'register_email_verify')
            && ($route != 'register_submited')
            && ($route != 'register')
            && ($request->getMethod() != 'POST')
        ) {
            $request->getSession()->invalidate();
            $this->container->get('security.token_storage')->setToken(null);

            $goto = $this->container->get('router')->generate('register_submited', array(
                'id' => $user['id'], 'hash' => $this->makeHash($user),
            ));

            $response = new RedirectResponse($goto, '302');
            $response->headers->setCookie(new Cookie('REMEMBERME', ''));
            $event->setResponse($response);
        }

        $loginBind = $this->getSettingService()->get('login_bind');

        if (empty($loginBind['login_limit'])) {
            return;
        }

        $user = $this->getUserService()->getUser($user['id']);

        if (empty($user['loginSessionId']) || strlen($user['loginSessionId']) <= 0) {
            $sessionId = $request->getSession()->getId();
            $this->getUserService()->rememberLoginSessionId($user['id'], $sessionId);
            $this->getUserService()->markLoginSuccess($user['id'], $request->getClientIp());

            return;
        }

        $REMEMBERME = $request->cookies->get('REMEMBERME');

        $userLoginToken = $session->getId();
        if (empty($userLoginToken) && !empty($REMEMBERME)) {
            return;
        }

        if ($userLoginToken != $user['loginSessionId']) {
            $magic = $this->getSettingService()->get('magic');

            if ((!empty($magic['login_limit'])) && ($request->isXmlHttpRequest())) {
                $response = new Response(array('error' => array('code' => UserException::LIMIT_LOGIN)), 403);
                $response->headers->clearCookie('REMEMBERME');
                $response->send();
            }
            $request->getSession()->invalidate();

            $this->container->get('security.token_storage')->setToken(null);

            $goto = $this->container->get('router')->generate('login');

            $response = new RedirectResponse($goto, '302');
            setcookie('REMEMBERME', '', -1);
            $this->container->get('session')->getFlashBag()->add('danger', '此帐号已在别处登录，请重新登录');

            $event->setResponse($response);
        }
    }

    private function makeHash($user)
    {
        $string = $user['id'].$user['email'].$this->container->getParameter('secret');

        return md5($string);
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    protected function getAuthService()
    {
        return $this->getBiz()->service('User:AuthService');
    }

    protected function getBiz()
    {
        return $this->container->get('biz');
    }

    protected function getUser()
    {
        $biz = $this->getBiz();

        return $biz['user'];
    }
}
