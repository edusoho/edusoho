<?php
namespace Topxia\WebBundle\Handler;
 
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Topxia\Service\Common\ServiceKernel;
 
class BaseAuthenticationHandler extends DefaultAuthenticationFailureHandler
{

    protected function checkLoginForbidden($request)
    {

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

        $result = $this->getUserService()->checkLoginForbidden($user ? $user['id'] : 0, $request->getClientIp());
        if ($result['status'] == 'error') {
            switch ($result['code']) {
                case 'max_ip_failed_limit':
                    $result['message'] = '帐号或密码输入错误过多，请在１个小时后再试。';
                    break;
                case 'max_failed_limit':
                    $result['message'] = "帐号或密码输入错误过多，请在{$setting['temporary_lock_minutes']}后再试，您可以通过找回并重置密码来解除封禁。";
                    break;
                default:
                    $result['message'] = "帐号或密码输入错误过多，您已被禁止登录。";
                    break;
            }
        } else {
            $result['message'] = '';
        }

        $result['user'] = $user;

        return $result;
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