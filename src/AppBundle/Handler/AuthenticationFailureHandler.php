<?php

namespace AppBundle\Handler;

use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $request->getSession()->set('_target_path', $request->request->get('_target_path'));

        if (!$exception instanceof BadCredentialsException) {
            goto end;
        }

        $default = array(
            'temporary_lock_enabled' => 0,
            'temporary_lock_allowed_times' => 5,
            'ip_temporary_lock_allowed_times' => 20,
            'temporary_lock_minutes' => 20,
        );
        $setting = $this->getSettingService()->get('login_bind', array());
        $setting = array_merge($default, $setting);

        if (empty($setting['temporary_lock_enabled'])) {
            goto end;
        }

        $forbidden = AuthenticationHelper::checkLoginForbidden($request);

        if ($forbidden['status'] == 'error') {
            $message = $forbidden['message'];
            $exception = new AuthenticationException($message);
        } else {
            $failed = $this->getUserService()->markLoginFailed($forbidden['user'] ? $forbidden['user']['id'] : 0, $request->getClientIp());

            if ($forbidden['user']) {
                if ($failed['ipFaildCount'] >= $setting['ip_temporary_lock_allowed_times']) {
                    $message = $this->getServiceKernel()->trans('您当前IP下帐号或密码输入错误过多，请在%settingTemporaryLockMinutes%分钟后再试。', array('%settingTemporaryLockMinutes%' => $setting['temporary_lock_minutes']));
                } elseif ($failed['leftFailedCount']) {
                    $message = $this->getServiceKernel()->trans('帐号或密码错误，您还有%failedLeftFailedCount%次输入机会', array('%failedLeftFailedCount%' => $failed['leftFailedCount']));
                } else {
                    $message = $this->getServiceKernel()->trans('帐号或密码输入错误过多，请在%settingTemporaryLockMinutes%分钟后再试，您可以通过找回并重置密码来解除封禁。', array('%settingTemporaryLockMinutes%' => $setting['temporary_lock_minutes']));
                }

                $exception = new AuthenticationException($message);
            }
        }

        end:

        if ($request->isXmlHttpRequest()) {
            $content = array(
                'success' => false,
                'message' => empty($message) ? $exception->getMessage() : $message,
            );

            return new JsonResponse($content, 400);
        }

        return parent::onAuthenticationFailure($request, $exception);
    }

    private function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
