<?php

namespace AppBundle\Listener;

use AppBundle\Controller\OAuth2\OAuthUser;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KernelResponseListener extends AbstractSecurityDisabledListener
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();

        if ($this->isSecurityDisabledRequest($this->container, $request)) {
            return;
        }

        $currentUser = $this->getUserService()->getCurrentUser();

        $auth = $this->getSettingService()->get('auth');

        if ($currentUser->isLogin() && !in_array('ROLE_SUPER_ADMIN', $currentUser['roles'])
            && isset($auth['fill_userinfo_after_login']) && $auth['fill_userinfo_after_login'] && isset($auth['registerSort'])
        ) {
            $whiteList = $this->getRouteWhiteList();

            if (in_array($request->getPathInfo(), $whiteList) || strstr($request->getPathInfo(), '/admin')
                || strstr($request->getPathInfo(), '/register/submited') || strstr($request->getPathInfo(), '/mapi_v2')
            ) {
                return;
            }

            $isFillUserInfo = $this->checkUserinfoFieldsFill($currentUser);
            //TODO 因为移动端的第三方注册做到了web端，所以增加一个 skip 判断，如果以后移动端端这块业务剥离，这个判断要去掉
            if (!$isFillUserInfo && !$request->getSession()->get(OAuthUser::SESSION_SKIP_KEY)) {
                $url = $this->container->get('router')->generate('login_after_fill_userinfo', array('goto' => $this->getTargetPath($request)));

                $response = new RedirectResponse($url);
                $event->setResponse($response);

                return;
            }
        }
    }

    protected function getRouteWhiteList()
    {
        return array(
            '/fill/userinfo', '/login', '/logout', '/login_check', '/register/mobile/check',
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
        );
    }

    protected function generateUrl($router, $params = array(), $withHost = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->container->get('router')->generate($router, $params, $withHost);
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

        if ($targetPath == $this->generateUrl('login', array(), UrlGeneratorInterface::ABSOLUTE_URL)) {
            return $this->generateUrl('homepage');
        }

        $url = explode('?', $targetPath);

        if ($url[0] == $this->generateUrl('partner_logout', array(), UrlGeneratorInterface::ABSOLUTE_URL)) {
            return $this->generateUrl('homepage');
        }

        if ($url[0] == $this->generateUrl('password_reset_update', array(), UrlGeneratorInterface::ABSOLUTE_URL)) {
            $targetPath = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $targetPath;
    }

    private function checkUserinfoFieldsFill($user)
    {
        $auth = $this->getSettingService()->get('auth');
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['email'] = strstr($user['email'], '@edusoho.net') ? '' : $user['email'];
        $userProfile['mobile'] = empty($auth['mobileSmsValidate']) ? $userProfile['mobile'] : $user['verifiedMobile'];

        $isFillUserInfo = true;

        if ($auth['registerSort']) {
            foreach ($auth['registerSort'] as $key => $val) {
                if (!$userProfile[$val]) {
                    $isFillUserInfo = false;
                }
            }
        }

        return $isFillUserInfo;
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    protected function getBiz()
    {
        return $this->container->get('biz');
    }
}
