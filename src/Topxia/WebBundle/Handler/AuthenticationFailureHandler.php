<?php
 
namespace Topxia\WebBundle\Handler;
 
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Topxia\WebBundle\Handler\AuthenticationHelper;
use Topxia\Service\Common\ServiceKernel;
 
class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $request->getSession()->set('_target_path',  $request->request->get('_target_path'));
        if ($exception->getMessage() != "Bad credentials") {
            goto end;
        }

        $forbidden = AuthenticationHelper::checkLoginForbidden($request);

        if ($forbidden['status'] == 'error') {
            $message = $forbidden['message'];
            $exception = new AuthenticationException($message);
        } else {
            $failed = $this->getUserService()->markLoginFailed($forbidden['user'] ? $forbidden['user']['id'] : 0, $request->getClientIp());
            if ($forbidden['user']) {
                if ($failed['leftFailedCount']) {
                    $message = "帐号或密码错误，您还有{$failed['leftFailedCount']}次输入机会";
                } else {
                    $setting = $this->getSettingService()->get('login_bind', array());
                    $setting = array_merge(array('temporary_lock_minutes' => 20), $setting);
                    $message = "帐号或密码输入错误过多，请在{$setting['temporary_lock_minutes']}后再试，您可以通过找回并重置密码来解除封禁。";
                }
                $exception = new AuthenticationException($message);
            } else {
                $message = $exception->getMessage();
            }
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

    private function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}