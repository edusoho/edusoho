<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class LoginController extends BaseController
{

    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if ($user->isLogin()) {
            return $this->createMessageResponse('info', '你已经登录了', null, 3000, $this->generateUrl('homepage'));
        }

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render('TopxiaWebBundle:Login:index.html.twig',array(
            'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
            'targetPath' => $this->getTargetPath($request),
        ));
    }

    private function getTargetPath($request)
    {
        if ($request->getSession()->has('_target_path')) {
            $targetPath = $request->getSession()->get('_target_path');
        } else {
            $targetPath = $request->headers->get('Referer');
        }

        if ($targetPath == $this->generateUrl('login', array(), true)) {
            return $this->generateUrl('homepage');
        }

        $url = explode('?', $targetPath);

        if ($url[0] == $this->generateUrl('partner_logout', array(), true)) {
            return $this->generateUrl('homepage');
        }

        
        if ($url[0] == $this->generateUrl('password_reset_update', array(), true)) {
            $targetPath = $this->generateUrl('homepage', array(), true);
        }

        return $targetPath;
    }

    public function ajaxAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:Login:ajax.html.twig', array(
            'targetPath' => $this->getTargetPath($request),
        ));
    }

    public function checkEmailAction(Request $request)
    {
        $email = $request->query->get('value');
        $user = $this->getUserService()->getUserByEmail($email);
        if ($user) {
            $response = array('success' => true, 'message' => '该Email地址可以登录');
        } else {
            $response = array('success' => false, 'message' => '该Email地址尚未注册');
        }
        return $this->createJsonResponse($response);
    }
}
