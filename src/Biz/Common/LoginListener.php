<?php

namespace Biz\Common;

use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Custom login listener.
 */
class LoginListener implements AuthenticationSuccessHandlerInterface
{
    /**
     * @var Biz
     */
    private $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

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
        if ($request->isXmlHttpRequest()) {
            $result = array('success' => true);

            return new Response(json_encode($result));
        } else {
            $referer = $request->headers->get('referer');
            if (strpos($referer, '/login')) {
                $referer = str_replace('/login', '/my', $referer);
            }

            return new RedirectResponse($referer);
        }
    }

    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }
}
