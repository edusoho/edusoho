<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use AppBundle\Component\OAuthClient\OAuthClientFactory;
use Endroid\QrCode\QrCode;
use Symfony\Component\HttpFoundation\Response;
use Biz\User\CurrentUser;

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
                'duration' => 60,
            )
        );
        $url = $host.'/h5/index.html#/login/qrcode?loginToken='.$token['token'].'&host='.$host;

        $qrCode = new QrCode();
        $qrCode->setText($url);
        $qrCode->setSize(150);
        $qrCode->setPadding(10);
        $img = $qrCode->get('png');

        return $this->createJsonResponse(array(
            'qrcode' => 'data:image/png;base64,' . base64_encode($img),
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
        } elseif (self::FACE_TOKEN_STATUS_CREATED == $faceLoginToken['data']['status']) {
            $response = array(
                'status' => self::FACE_TOKEN_STATUS_PROCESSING,
            );
        } else {
            if (!empty($faceLoginToken['data']['lastFailed'])) {
                $response = array(
                    'status' => self::FACE_TOKEN_STATUS_FAILURES
                );
            } else {
                $response = array(
                    'status' => $faceLoginToken['data']['status'],
                );
            }
            if (self::FACE_TOKEN_STATUS_SUCCESS == $faceLoginToken['data']['status']) {
                $response['url'] = $this->generateUrl('login_parse_face_token', array('token' => $token, 'goto' => $request->query->get('goto')));
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
                'duration' => 1,
                'message' => '二维码已失效，正跳转到首页',
            ));

            return new Response($content, '302');
        } elseif (empty($faceLoginToken['data']['status']) || self::FACE_TOKEN_STATUS_SUCCESS != $faceLoginToken['data']['status']) {
            $content = $this->renderView('default/message.html.twig', array(
                'type' => 'error',
                'goto' => $this->generateUrl('homepage', array(), true),
                'duration' => 1,
                'message' => '人脸认证未成功，正跳转到首页',
            ));

            return new Response($content, '302');
        }

        $currentUser = $this->getCurrentUser();

        if (!empty($faceLoginToken['data']['user']['id']) && (!$currentUser->isLogin() || $currentUser['id'] != $faceLoginToken['data']['user']['id'])) {
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
            return $this->createMessageResponse('info', '你已经登录了', null, 3000, $this->generateUrl('homepage'));
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
}
