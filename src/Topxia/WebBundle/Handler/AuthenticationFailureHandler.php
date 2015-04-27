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
            if ($failed['failedCount']) {
                $leftCount = $setting['temporary_lock_allowed_times'] - $failed['failedCount'];
                $leftCount = $leftCount > 0 ? $leftCount : 0;
                $message = "帐号或密码错误，您还有{$leftCount}次输入机会";
                $exception = new AuthenticationException($message);
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
}