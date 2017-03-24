<?php

namespace AppBundle\Controller;

use Biz\Role\Util\PermissionBuilder;
use Biz\User\CurrentUser;
use Endroid\QrCode\QrCode;
use Biz\User\Service\UserService;
use Biz\User\Service\TokenService;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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

        if (strpos(strtolower($request->headers->get('User-Agent')), 'kuozhi') > -1) {
            return $this->redirect($token['data']['appUrl']);
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
        $currentUser = $this->getCurrentUser();
        try {
            $switchUser = new CurrentUser();
            $switchUser->fromArray($this->getUserService()->getUserByType('scheduler'));
            $this->switchUser($request, $switchUser);
            $this->getBiz()->service('Crontab:CrontabService')->scheduleJobs();
            $this->switchUser($request, $currentUser);

            return $this->createJsonResponse(true);
        } catch (\Exception $e) {
            $token = new UsernamePasswordToken($currentUser, null, 'main', $currentUser['roles']);
            $this->container->get('security.token_storage')->setToken($token);
            throw $e;
        }
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
}
