<?php
 
namespace Topxia\Service\Common;
 
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Topxia\Service\Common\BaseService;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Custom login listener.
 */
class LoginListener extends BaseService implements AuthenticationSuccessHandlerInterface
{


	/**
	 * Do the magic.
	 * 
	 * @param InteractiveLoginEvent $event
	 */
	public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
	{
		// if ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY') || $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
		// 	$request = $event->getRequest();
		// 	$user = $event->getAuthenticationToken()->getUser();
		// 	$loginInfo = array('loginedIp'=>$request->getClientIp(),'loginedTime'=> time());
		// 	$this->getUserService()->updateLoginInfo($user['id'],$loginInfo);
		// 	$this->getLogService()->info('登录模块','用户登录',"用户登录成功！");
		// }
	}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {       
			$user = $token->getUser();
		$loginInfo = array('loginIp'=>$request->getClientIp(),'loginTime'=> time());
		// $this->getUserService()->updateLoginInfo($user['id'],$loginInfo);
		// $this->getLogService()->info('登录模块','用户登录',"用户登录成功！");
   		if ($request->isXmlHttpRequest()) {
        	$result = array('success' => true);
        	return new Response(json_encode($result));
    	} else {
    		 $referer = $request->headers->get('referer');
    		 if(strpos($referer,'/login')){
    		 	$referer =  str_replace('/login','/my',$referer);
    		 }
        	 return new RedirectResponse($referer);
    	}			
    }

    private function getUserService()
    {
      	return $this->createService('User.UserService');
    }

	protected function getLogService(){
		 return $this->createService('System.LogService');
	}    
}