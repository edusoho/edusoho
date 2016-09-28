<?php 
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Topxia\Component\OAuthClient\OAuthClientFactory;
use Topxia\WebBundle\Controller\LoginController as BaseLoginController;

class LoginController extends BaseLoginController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if ($user->isLogin()) {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('你已经登录了'), null, 3000, $this->generateUrl('homepage'));
        }

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        if ($this->getWebExtension()->isMicroMessenger() && $this->setting('login_bind.enabled', 0) && $this->setting('login_bind.weixinmob_enabled', 0)) {
            $inviteCode = $request->query->get('inviteCode');
            return $this->redirect($this->generateUrl('login_bind', array('type' => 'weixinmob', '_target_path' => $this->getTargetPath($request), 'inviteCode' => $inviteCode)));
        }

        return $this->render('CustomWebBundle:Login:index.html.twig', array(
            'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
            '_target_path'  => $this->getTargetPath($request),
            'local' => $request->getLocale()
        ));
    }
}