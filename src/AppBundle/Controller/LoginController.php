<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use AppBundle\Component\OAuthClient\OAuthClientFactory;
use Endroid\QrCode\QrCode;
use Symfony\Component\HttpFoundation\Response;
use Biz\User\CurrentUser;
use Biz\System\Service\SettingService;

class LoginController extends BaseController
{
    const FACE_TOKEN_STATUS_SUCCESS = 'successed'; //认证成功
    const FACE_TOKEN_STATUS_CREATED = 'created'; //已创建
    const FACE_TOKEN_STATUS_EXPIRED = 'expired'; //已过期
    const FACE_TOKEN_STATUS_PROCESSING = 'processing'; //认证中
    const FACE_TOKEN_STATUS_FAILURES = 'failures'; //多次认证失败

    public function qrcodeAction(Request $request)
    {
        $host = $request->getSchemeAndHttpHost();
        $token = $this->getTokenService()->makeToken(
            'face_login',
            array(
                'userId' => 0,
                'data' => array(),
                'times' => 0,
                'duration' => 240,
            )
        );
        $url = $host.'/h5/index.html#/login/qrcode?loginToken='.$token['token'].'&host='.$host;

        $qrCode = new QrCode();
        $qrCode->setText($url);
        $qrCode->setSize(150);
        $qrCode->setPadding(10);
        $img = $qrCode->get('png');

        return $this->createJsonResponse(array(
            'qrcode' => 'data:image/png;base64,'.base64_encode($img),
            'token' => $token['token'],
        ));
    }

    public function faceTokenAction(Request $request, $token)
    {
        $faceLoginToken = $this->getTokenService()->verifyToken('face_login', $token);

        if (!$faceLoginToken) {
            $response = array(
                'status' => self::FACE_TOKEN_STATUS_EXPIRED,
            );
        } elseif (empty($faceLoginToken['data'])) {
            $response = array(
                'status' => self::FACE_TOKEN_STATUS_CREATED,
            );
        } elseif (!empty($faceLoginToken['data']['lastFailed'])) {
            $response = array(
                'status' => self::FACE_TOKEN_STATUS_FAILURES,
            );
        } else {
            switch ($faceLoginToken['data']['status']) {
                case self::FACE_TOKEN_STATUS_CREATED:
                    $response = array(
                        'status' => self::FACE_TOKEN_STATUS_PROCESSING,
                    );
                    break;

                case self::FACE_TOKEN_STATUS_SUCCESS:
                    $response = array(
                        'status' => $faceLoginToken['data']['status'],
                        'url' => $this->generateUrl('login_parse_face_token', array('token' => $token, 'goto' => $request->query->get('goto'))),
                    );
                    break;

                default:
                    $response = array(
                        'status' => $faceLoginToken['data']['status'],
                    );
                    break;
            }
        }

        return $this->createJsonResponse($response);
    }

    public function parseFaceTokenAction(Request $request, $token)
    {
        $faceLoginToken = $this->getTokenService()->verifyToken('face_login', $token);
        if (empty($faceLoginToken)) {
            $content = $this->renderView('default/message.html.twig', array(
                'type' => 'error',
                'goto' => $this->generateUrl('homepage', array(), true),
                'duration' => 1000,
                'message' => 'user.login.sts_qrcode_invalid',
            ));

            return new Response($content, '302');
        } elseif (empty($faceLoginToken['data']['status']) || self::FACE_TOKEN_STATUS_SUCCESS != $faceLoginToken['data']['status']) {
            $content = $this->renderView('default/message.html.twig', array(
                'type' => 'error',
                'goto' => $this->generateUrl('homepage', array(), true),
                'duration' => 1000,
                'message' => 'user.login.sts_discovery_failed',
            ));

            return new Response($content, '302');
        }

        $currentUser = $this->getCurrentUser();

        if (!empty($faceLoginToken['data']['user']['id']) && (!$currentUser->isLogin() || $faceLoginToken['data']['user']['id'] != $currentUser['id'])) {
            $user = $this->getUserService()->getUser($faceLoginToken['data']['user']['id']);
            $currentUser = new CurrentUser();
            $currentUser->fromArray($user);
            $this->switchUser($request, $currentUser);
            $this->getTokenService()->destoryToken($token);
        }

        $goto = $request->query->get('goto');
        if (empty($goto)) {
            $goto = $this->generateUrl('homepage', array(), true);
        }

        return $this->redirect($goto);
    }

    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if ($user->isLogin()) {
            return $this->createMessageResponse('info', '你已经登录了', null, 3000, $this->getTargetPath($request));
        }

        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(Security::AUTHENTICATION_ERROR);
        }

        if ($this->getWebExtension()->isWechatLoginBind()) {
            return $this->redirect($this->generateUrl('login_bind', array('type' => 'weixinmob', '_target_path' => $this->getTargetPath($request))));
        }

        return $this->render('login/index.html.twig', array(
            'last_username' => $request->getSession()->get(Security::LAST_USERNAME),
            'error' => $error,
            '_target_path' => $this->getTargetPath($request),
        ));
    }

    public function ajaxAction(Request $request)
    {
        return $this->render('login/ajax.html.twig', array(
            '_target_path' => $this->getTargetPath($request),
        ));
    }

    public function checkEmailAction(Request $request)
    {
        $email = $request->query->get('value');
        $user = $this->getUserService()->getUserByEmail($email);

        if ($user) {
            $response = array('success' => true, 'message' => '该Email地址可以登录');
        } else {
            $response = array('success' => false, 'message' => '该Email地址尚未注册');
        }

        return $this->createJsonResponse($response);
    }

    public function oauth2LoginsBlockAction($targetPath, $displayName = true)
    {
        $clients = OAuthClientFactory::clients();

        return $this->render('login/oauth2-logins-block.html.twig', array(
            'clients' => $clients,
            'targetPath' => $targetPath,
            'displayName' => $displayName,
        ));
    }

    public function wechatQrcodeAction(Request $request)
    {
        $wechatSetting = $this->getSettingService()->get('wechat', array());
        if (!empty($wechatSetting['wechat_notification_enabled'])) {
            $loginUrl = $this->generateUrl('login_bind', array('type' => 'weixinmob', '_target_path' => $this->generateUrl('common_wechat_subscribe_wap')), true);
            $response = array(
                'img' => $this->generateUrl('common_qrcode', array('text' => $loginUrl), true),
            );

            return $this->createJsonResponse($response);
        }

        return $this->createJsonResponse(array('img' => ''));
    }

    protected function getTargetPath(Request $request)
    {
        if ($request->query->get('goto')) {
            $targetPath = $request->query->get('goto');
        } elseif ($request->getSession()->has('_target_path')) {
            $targetPath = $request->getSession()->get('_target_path');
        } else {
            $targetPath = $request->headers->get('Referer');
        }

        if ($targetPath == $this->generateUrl('login', array(), true)) {
            return $this->generateUrl('homepage');
        }

        $url = explode('?', $targetPath);

        if ($url[0] == $this->generateUrl('partner_logout', array(), true)) {
            return $this->generateUrl('homepage');
        }

        if ($url[0] == $this->generateUrl('password_reset_update', array(), true)) {
            $targetPath = $this->generateUrl('homepage', array(), true);
        }

        if (0 === strpos($targetPath, '/app.php')) {
            $targetPath = str_replace('/app.php', '', $targetPath);
        }

        return $targetPath;
    }

    protected function getWebExtension()
    {
        return $this->container->get('web.twig.extension');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
