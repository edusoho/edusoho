<?php
 
namespace Topxia\WebBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Topxia\Service\Common\ServiceKernel;

class UserLoginTokenListener
{
	public function __construct($container)
    {
        $this->container = $container;
    }

    public function onGetUserLoginListener (GetResponseEvent $event)
    {
    	$request = $event->getRequest();
        $session = $request->getSession();
        if (empty($session)) {
            return;
        }

        $userLoginToken = $request->getSession()->getId();
        $user = $this->getUserService()->getCurrentUser();

        if(isset($user['locked']) && $user['locked'] == 1){
            $this->container->get("security.context")->setToken(null);
            setcookie("REMEMBERME");
            return;
        }

        if (!$user->islogin()) {
            return;
        }

        $auth = $this->getSettingService()->get('auth');

        if($auth && array_key_exists('email_enabled',$auth) 
        	&& $user["createdTime"] > $auth["setting_time"] && $user["emailVerified"] == 0){
           if($auth['email_enabled'] == 'opened'){
	$goto = $this->generateUrl('register_submited', array(
                'id' => $user['id'], 'hash' => $this->makeHash($user),
                'goto' => $this->getTargetPath($request),
            ));
           }
        }

        $loginBind = $this->getSettingService()->get('login_bind');
        if (empty($loginBind['login_limit'])) {
            return;
        }

        if (empty($user['loginSessionId'])) {
            return;
        }

    	if ($userLoginToken != $user['loginSessionId']) {

            $request->getSession()->invalidate();
            $this->container->get("security.context")->setToken(null);

            $goto = $this->container->get('router')->generate('login');

            $response = new RedirectResponse($goto, '302');
            $response->headers->setCookie(new Cookie("REMEMBERME", ''));

            $this->container->get('session')->getFlashBag()->add('danger', '此帐号已在别处登录，请重新登录');

    		$event->setResponse($response);
    	}
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}