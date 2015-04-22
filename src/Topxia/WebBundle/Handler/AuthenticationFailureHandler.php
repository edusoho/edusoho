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

        if ($exception->getMessage() != "Bad credentials") {
            goto end;
        }

        $setting = $this->getSettingService()->get('login_bind', array());
        $default = array(
            'temporary_lock_enabled' => 0,
            'temporary_lock_allowed_times' => 5,
            'temporary_lock_minutes' => 20,
        );
        $setting = array_merge($default, $setting);

        $username = $request->request->get('_username');
        $user = $this->getUserService()->getUserByNickname($username);
        if (empty($user)) {
            $user = $this->getUserService()->getUserByEmail($username);
        }

        $passResult = $this->getUserService()->checkLoginForbidden($user ? $user['id'], $request->getClientIp());
        if ($passResult['status'] == 'error') {
            switch ($passResult['status']) {
                case 'max_ip_failed_limit':
                    $message = '帐号或密码输入错误过多，请在１个小时后再试。';
                    break;
                case 'max_failed_limit':
                    $message = "帐号或密码输入错误过多，请在{$setting['temporary_lock_minutes']}后再试，您可以通过找回并重置密码来解除封禁。";
                    break;
                default:
                    $message = "您已被禁止登录。";
                    break;
            }

            $exception = new AuthenticationException($message);
        } else {
            $failed = $this->getUserService()->markLoginFailed($user ? $user['id'] : 0, $ip);
            if (!empty($failed['failed_count'])) {
                $leftCount = $setting['temporary_lock_allowed_times'] - $failed['failed_count'];
                $leftCount = $leftCount > 0 ? $leftCount : 0;
                $message = "帐号或密码错误，您还有{$leftTimes}次输入机会";
                $exception = new AuthenticationException($message);
            }
            
        }



        // 帐号或密码错误，您还有{$leftTimes}次输入机会









        $request->getSession()->set('_target_path',  $request->request->get('_target_path'));
        $username = $request->request->get('_username');
        $user = $this->getUserService()->getUserByNickname($username);
        if ($user == 0) {
            $user = $this->getUserService()->getUserByEmail($username);
        }

        if ($user != 0){ 
            if (time() > $user['lastPasswordFailTime'] + $loginConnect['temporary_lock_minutes']*60){
                $user['consecutivePasswordErrorTimes'] = 0;
            }
            $leftTimes = $loginConnect['temporary_lock_allowed_times']-$user['consecutivePasswordErrorTimes']-1;
            $leftTimesMessage = ($leftTimes != 0)?"帐号或密码错误，您还有{$leftTimes}次输入机会":"帐号或密码输入错误已到{$loginConnect['temporary_lock_allowed_times']}次，帐号将会封禁{$loginConnect['temporary_lock_minutes']}分钟，您可以通过找回并重置密码来解除封禁。";
            
            if ( $exception->getMessage() == "Bad credentials" && $loginConnect['temporary_lock_enabled'] == 1 ){
                $this->getUserService()->userLoginFail($user, $loginConnect['temporary_lock_allowed_times'], $loginConnect['temporary_lock_minutes']); 
                $exception = new AuthenticationException($leftTimesMessage);
            }
        }
        
        $this->getLogService()->info('user', 'login_fail', "用户名：{$username}，登录失败：{$message}");

        if ($request->isXmlHttpRequest()) {
            $content = array(
                'success' => false,
                'message' => $message,
            );
            return new JsonResponse($content, 400);
        }

        end:
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