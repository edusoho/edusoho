<?php
 
namespace Topxia\WebBundle\Handler;
 
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Topxia\Service\Common\ServiceKernel;
 
class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{

    protected $translator;

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $loginConnect = $this->getSettingService()->get('login_bind', array());
        $default = array(
            'temporary_lock_enabled' => 0,
            'temporary_lock_allowed_times' => 3,
            'temporary_lock_minutes' => 20,
        );

        $loginConnect = array_merge($default, $loginConnect);

        $message = $this->translator->trans($exception->getMessage());

        if ($loginConnect['temporary_lock_enabled'] == 1){
            $message .=", 连续输错{$loginConnect['temporary_lock_allowed_times']}次密码, 将被暂锁{$loginConnect['temporary_lock_minutes']}分钟." ;
        }

        if ($request->isXmlHttpRequest()) {
            $content = array(
                'success' => false,
                'message' => $message,
            );
            return new JsonResponse($content, 400);
        }

        $request->getSession()->set('_target_path',  $request->request->get('_target_path'));

        $username = $request->request->get('_username');

        if ($loginConnect['temporary_lock_enabled'] == 1){

            $user = $this->getUserService()->getUserByNickname($username);
            if ($user == 0) {
                $user = $this->getUserService()->getUserByEmail($username);
            }

            $this->getUserService()->userLoginFail($user, $loginConnect['temporary_lock_allowed_times'], $loginConnect['temporary_lock_minutes']); 
        }

        $this->getLogService()->info('user', 'login_fail', "用户名：{$username}，登录失败：{$message}");

        if ( $exception->getMessage() == "Bad credentials" && $loginConnect['temporary_lock_enabled'] == 1 ){
            $exception = new AuthenticationException("帐号或密码不正确, 连续输错{$loginConnect['temporary_lock_allowed_times']}次密码, 将被暂锁{$loginConnect['temporary_lock_minutes']}分钟.");
        }

        return parent::onAuthenticationFailure($request, $exception);
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    private function getLogService()
    {
        return ServiceKernel::instance()->createService('System.LogService');
    }

    private function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}