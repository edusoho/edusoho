<?php

namespace AppBundle\Listener;

use Biz\System\Service\SettingService;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * Stores the locale of the user in the session after the
 * login. This can be used by the LocaleListener afterwards.
 */
class UserLoginCaptchaListener
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param GetResponseEvent $event
     * @throws Exception
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($request->getMethod() === 'POST') {
            $loginSetting = $this->getSettingService()->get('login_bind', []);
            if (!empty($loginSetting['login_captcha_enable']) && '/login_check' == $request->getPathInfo()) {
                $biz = $this->getBiz();
                $bizDragCaptcha = $biz['biz_drag_captcha'];

                $dragCaptchaToken = empty($request->request->get('dragCaptchaToken')) ? '' : $request->request->get('dragCaptchaToken');
                try {
                    $bizDragCaptcha->check($dragCaptchaToken);
                } catch (Exception $e) {
                    throw new AccessDeniedHttpException($e->getMessage(), $e);
                }
            }
        }
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    protected function trans($id, array $parameters = array())
    {
        return $this->container->get('translator')->trans($id, $parameters);
    }

    protected function getBiz()
    {
        return $this->container->get('biz');
    }
}
