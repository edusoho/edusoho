<?php

namespace AppBundle\Handler;

use AppBundle\Common\EncryptionToolkit;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Topxia\Service\Common\ServiceKernel;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $forbidden = AuthenticationHelper::checkLoginForbidden($request);

        if ('error' === $forbidden['status']) {
            throw new AuthenticationException($forbidden['message']);
        }

        $currentUser = $this->getServiceKernel()->getCurrentUser();
        if(!$currentUser->isAccountNonLocked()) {
            throw new AuthenticationException('账号已被禁用');
        }
        $password = EncryptionToolkit::XXTEADecrypt(base64_decode($request->request->get('_password')), 'EduSoho');
        if ($this->getUserService()->validatePassword($password)) {
            $this->getUserService()->updateUser($currentUser->getId(), ['passwordUpgraded' => 1]);
        } else {
            $request->getSession()->set('needUpgradePassword', 1);
        }

        if ($request->isXmlHttpRequest()) {
            $content = [
                'success' => true,
            ];

            return new JsonResponse($content, 200);
        }

        return parent::onAuthenticationSuccess($request, $token);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
