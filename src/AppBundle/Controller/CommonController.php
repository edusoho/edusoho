<?php

namespace AppBundle\Controller;

use AppBundle\Common\Exception\AccessDeniedException;
use Biz\User\CurrentUser;
use Biz\WeChat\Service\WeChatService;
use Endroid\QrCode\QrCode;
use Biz\User\Service\UserService;
use Biz\User\Service\TokenService;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommonController extends BaseController
{
    public function qrcodeAction(Request $request)
    {
        $text = $request->get('text');
        $qrCode = new QrCode();
        $qrCode->setText($text);
        $qrCode->setSize(250);
        $qrCode->setPadding(10);
        $img = $qrCode->get('png');

        $headers = array(
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="qrcode.png"',
        );

        return new Response($img, 200, $headers);
    }

    public function weChatOfficialSubscribeAction(Request $request)
    {
        $weChatSetting = $this->getSettingService()->get('wechat', array());
        if (empty($weChatSetting['wechat_notification_enabled'])) {
            throw new AccessDeniedException('无法获取微信公众号二维码');
        }

        $user = $this->getCurrentUser();
        if ($user->isLogin()) {
            $weChatUser = $this->getWeChatService()->getOfficialWeChatUserByUserId($user['id']);
            if (!empty($weChatUser['isSubscribe'])) {
                return $this->redirect($this->generateUrl('homepage'));
            }
        }

        return $this->render('common/wechat-subscribe.html.twig', array(
            'qrcodeUrl' => $weChatSetting['account_code'],
        ));
    }

    public function parseQrcodeAction(Request $request, $token)
    {
        $token = $this->getTokenService()->verifyToken('qrcode', $token);
        if (empty($token) || !isset($token['data']['url'])) {
            $content = $this->renderView('default/message.html.twig', array(
                'type' => 'error',
                'goto' => $this->generateUrl('homepage', array(), true),
                'duration' => 1,
                'message' => '二维码已失效，正跳转到首页',
            ));

            return new Response($content, '302');
        }

        $currentUser = $this->getCurrentUser();

        if (!empty($token['userId']) && !$currentUser->isLogin() && $currentUser['id'] != $token['userId']) {
            $user = $this->getUserService()->getUser($token['userId']);
            $currentUser = new CurrentUser();
            $currentUser->fromArray($user);
            $this->switchUser($request, $currentUser);
        }

        return $this->redirect($token['data']['url']);
    }

    public function crontabAction(Request $request)
    {
        $currentUserToken = $this->container->get('security.token_storage')->getToken();

        try {
            $switchUser = new CurrentUser();
            $switchUser->fromArray($this->getUserService()->getUserByType('system'));

            $this->switchUser($request, $switchUser);
            $this->getSchedulerService()->execute();
            $this->container->get('security.token_storage')->setToken($currentUserToken);

            return $this->createJsonResponse(true);
        } catch (\Exception $e) {
            $this->container->get('security.token_storage')->setToken($currentUserToken);

            return $this->createJsonResponse(false);
        }
    }

    public function mobileQrcodeAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if ($user->isLogin()) {
            $tokenFields = array(
                'userId' => $user['id'],
                'duration' => 3600 * 24 * 30,
                'times' => 1,
            );

            $token = $this->getTokenService()->makeToken('mobile_login', $tokenFields);

            $url = $request->getSchemeAndHttpHost().'/mapi_v2/User/loginWithToken?token='.$token['token'];
        } else {
            $url = $request->getSchemeAndHttpHost().'/mapi_v2/School/loginSchoolWithSite?v=1';
        }

        $qrCode = new QrCode();
        $qrCode->setText($url);
        $qrCode->setSize(215);
        $qrCode->setPadding(10);
        $img = $qrCode->get('png');

        $headers = array('Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="image.png"', );

        return new Response($img, 200, $headers);
    }

    public function dragCaptchaAction($token)
    {
        $biz = $this->getbiz();
        $dragCaptcha = $biz['biz_drag_captcha'];
        $result = $dragCaptcha->getBackground($token);

        $headers = array('Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="image.jpg"', );

        return new Response($result, 200, $headers);
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->getBiz()->service('User:TokenService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    protected function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }

    /**
     * @return WeChatService
     */
    protected function getWeChatService()
    {
        return $this->getBiz()->service('WeChat:WeChatService');
    }
}
