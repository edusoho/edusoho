<?php

namespace AppBundle\Listener;

use Biz\User\CurrentUser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KernelControllerListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        if (!$this->getCurrentUser()->isLogin()) {
            return;
        }

        $request = $event->getRequest();
        if ($request->isMethod('POST')) {
            return;
        }
        if (in_array($request->getPathInfo(), $this->getRouteWhiteList())
            || strstr($request->getPathInfo(), '/mapi_v2')
            || strstr($request->getPathInfo(), '/api')
            || strstr($request->getPathInfo(), '/drag_captcha')
            || strstr($request->getPathInfo(), '/h5_entry')
            || strstr($request->getPathInfo(), '/password/reset')
            || strstr($request->getPathInfo(), '/app/package_update')
        ) {
            return;
        }

        $this->checkMobileBind($event);

        $this->checkPasswordUpgrade($event);

        $this->checkPasswordInit($event);
    }

    private function checkMobileBind(FilterControllerEvent $event)
    {
        $currentUser = $this->getCurrentUser();
        if (!empty($currentUser['verifiedMobile'])) {
            return;
        }
        $request = $event->getRequest();
        if ('1' != $this->getSettingService()->node('cloud_sms.sms_enabled') || 'on' != $this->getSettingService()->node('cloud_sms.sms_bind')) {
            return;
        }
        $mobileBindMode = $this->getSettingService()->node('login_bind.mobile_bind_mode', 'constraint');
        if ('closed' === $mobileBindMode) {
            return;
        }
        if ('option' === $mobileBindMode && (isset($_COOKIE['is_skip_mobile_bind']) && 1 == $_COOKIE['is_skip_mobile_bind'])) {
            return;
        }
        $url = $this->generateUrl('settings_mobile_bind', ['goto' => $this->getTargetPath($request)]);
        $event->setController(function () use ($url) {
            return new RedirectResponse($url);
        });
    }

    private function checkPasswordInit(FilterControllerEvent $event)
    {
        $currentUser = $this->getCurrentUser();
        if (empty($currentUser['passwordInit'])) {
            $url = $this->generateUrl('password_init', ['goto' => $this->getTargetPath($event->getRequest())]);
            $event->setController(function () use ($url) {
                return new RedirectResponse($url);
            });
        }
    }

    private function checkPasswordUpgrade(FilterControllerEvent $event)
    {
        $currentUser = $this->getCurrentUser();
        $loginBind = $this->getSettingService()->get('login_bind');
        $hasUpgradedPassword = !empty($currentUser['passwordUpgraded']);
        $skipPasswordUpdate = $currentUser['roles'] === ['ROLE_USER'] && isset($loginBind['login_strong_pwd_enable']) && 0 == $loginBind['login_strong_pwd_enable'];
        if ($hasUpgradedPassword || $skipPasswordUpdate) {
            return;
        }
        $request = $event->getRequest();
        if (empty($request->getSession()->get('needUpgradePassword'))) {
            return;
        }
        $request->getSession()->getFlashBag()->add('danger', '检测到您当前密码等级较低，请重新设置密码');
        $url = $this->generateUrl('password_reset');
        $event->setController(function () use ($url) {
            return new RedirectResponse($url);
        });
    }

    protected function getRouteWhiteList()
    {
        return [
            '/login', '/logout', '/login_check', '/register/mobile/check',
            '/register/email/check', '/login/bind/weixinmob/newset',
            '/login/bind/weixinmob/existbind', '/login/bind/weixinweb/newset',
            '/login/bind/qq/newset', '/login/bind/weibo/newset', '/login/bind/renren/newset',
            '/login/bind/qq/exist', '/login/bind/weibo/exist', '/login/bind/renren/exist',
            '/login/bind/weixinweb/exist', '/login/bind/weixinmob/exist',
            '/login/bind/weixinmob/choose', '/login/bind/weixinmob/changetoexist',
            '/login/bind/qq/new', '/login/bind/weibo/new', '/login/bind/renren/new',
            '/login/bind/weixinmob/new', '/login/bind/weixinweb/new',
            '/partner/login', '/partner/logout',
            '/login/weixinmob', '/login/bind/weixinmob/existbind',
            '/captcha_num', '/register/captcha/check', '/edu_cloud/sms_send',
            '/edu_cloud/sms_check/sms_bind', '/settings/check_login_password',
            '/register/email_or_mobile/check', '/settings/bind_mobile',
            '/edu_cloud/sms_send_check_captcha', '/settings/mobile_bind', '/switch/language',
            '/scrm/buy/goods/callback', '/file/upload', '/file/img/crop', '/online/sample',
            '/settings/setup_password',
            '/password/init',
        ];
    }

    protected function getTargetPath($request)
    {
        if ($request->query->get('goto')) {
            $targetPath = $request->query->get('goto');
        } elseif ($request->getSession()->has('_target_path')) {
            $targetPath = $request->getSession()->get('_target_path');
        } else {
            $targetPath = $request->headers->get('Referer');
        }

        return $targetPath;
    }

    protected function generateUrl($router, $params = [], $withHost = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->container->get('router')->generate($router, $params, $withHost);
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return CurrentUser
     */
    protected function getCurrentUser()
    {
        return $this->getBiz()['user'];
    }

    protected function getBiz()
    {
        return $this->container->get('biz');
    }
}
