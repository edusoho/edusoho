<?php

namespace AppBundle\Listener;

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

        if ('1' != $this->getSettingService()->node('cloud_sms.sms_enabled') || 'on' != $this->getSettingService()->node('cloud_sms.sms_bind')) {
            return;
        }

        $request = $event->getRequest();

        $currentUser = $this->getBiz()['user'];
        $mobileBindMode = $this->getSettingService()->node('login_bind.mobile_bind_mode', 'constraint');

        if ($currentUser->isLogin() && 'closed' !== $mobileBindMode && empty($currentUser['verifiedMobile'])) {
            $whiteList = $this->getRouteWhiteList();

            if (in_array($request->getPathInfo(), $whiteList)
                || strstr($request->getPathInfo(), '/mapi_v2')
                || strstr($request->getPathInfo(), '/api')
                || strstr($request->getPathInfo(), '/drag_captcha')
                || strstr($request->getPathInfo(), '/admin')
                || strstr($request->getPathInfo(), '/h5_entry')
                || ('option' === $mobileBindMode && (isset($_COOKIE['is_skip_mobile_bind']) && 1 == $_COOKIE['is_skip_mobile_bind']))
            ) {
                return;
            }

            $url = $this->generateUrl('settings_mobile_bind', ['goto' => $this->getTargetPath($request)]);
            $event->setController(function () use ($url) {
                return new RedirectResponse($url);
            });

        }
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
            '/partner/discuz/api/notify', '/partner/phpwind/api/notify', '/partner/login', '/partner/logout',
            '/login/weixinmob', '/login/bind/weixinmob/existbind',
            '/captcha_num', '/register/captcha/check', '/edu_cloud/sms_send',
            '/edu_cloud/sms_check/sms_bind', '/settings/check_login_password',
            '/register/email_or_mobile/check', '/settings/bind_mobile',
            '/edu_cloud/sms_send_check_captcha', '/settings/mobile_bind', '/switch/language',
            '/scrm/buy/goods/callback', '/file/upload', '/file/img/crop', '/online/sample',
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

    protected function getBiz()
    {
        return $this->container->get('biz');
    }
}
