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
        $request->getSession()->set('_target_path',  $request->request->get('_target_path'));

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

        $passResult = $this->getUserService()->checkLoginForbidden($user ? $user['id'] : 0, $request->getClientIp());
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
            $leftCount = $setting['temporary_lock_allowed_times'] - $failed['failed_count'];
            $leftCount = $leftCount > 0 ? $leftCount : 0;
            $message = "帐号或密码错误，您还有{$leftTimes}次输入机会";
            $exception = new AuthenticationException($message);
        }

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