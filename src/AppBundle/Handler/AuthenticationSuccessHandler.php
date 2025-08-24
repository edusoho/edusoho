<?php

namespace AppBundle\Handler;

use AppBundle\Common\EncryptionToolkit;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Biz\User\Support\PasswordValidator;
use Biz\User\Support\RoleHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Topxia\Service\Common\ServiceKernel;

/**
 * 登录成功后的处理器
 */
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
        $passwordLevel = PasswordValidator::getLevel($password);
        if ($currentUser['passwordUpgraded'] != $passwordLevel) {
            $this->getUserService()->updateUser($currentUser->getId(), ['passwordUpgraded' => $passwordLevel]);
            $currentUser['passwordUpgraded'] = $passwordLevel;
        }

        if (RoleHelper::isStudent($currentUser['roles'])) {
            $loginBindSetting = $this->getSettingService()->get('login_bind');
            if (($loginBindSetting['student_weak_password_check'] ?? 0) && !PasswordValidator::isValidLevel($passwordLevel)) {
                $request->getSession()->set('needUpgradePassword', 1);
            }
        } else {
            if (!PasswordValidator::isStrongLevel($passwordLevel)) {
                $request->getSession()->set('needUpgradePassword', 1);
            }
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
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System:SettingService');
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
