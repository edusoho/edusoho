<?php

namespace AppBundle\Controller\OAuth2\Server;

use AppBundle\Controller\BaseController;
use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Biz\User\CurrentUser;
use Symfony\Component\HttpFoundation\Request;

class AuthorizationController extends BaseController
{
    public function authorizeAction(Request $request)
    {
        $user = $this->getCurrentUser();

        //尝试获取cookie登录
        if (!$user->isLogin()) {
            $this->authCookie($request);
            $user = $this->getCurrentUser();
        }

        if ($user->isLogin()) {
            return $this->getOAuth2Server()->handleAuthorizeRequest($this->getOAuth2Request(), $this->getOAuth2Response(), true, $user['id']);
        }

        $error = '';
        if ('POST' == $request->getMethod()) {
            try {
                return $this->handleAuthorizeRequest($request);
            } catch (\Exception $e) {
                $error = '账号或者密码错误';
            }
        }

        if (!$this->getOAuth2Server()->validateAuthorizeRequest($this->getOAuth2Request(), $this->getOAuth2Response())) {
            return $this->getOAuth2Server()->getResponse();
        }

        $scopes = array();
        foreach (explode(' ', $this->getOAuth2Request()->query->get('scope')) as $scope) {
            $scopes[] = $this->getOAuth2StorageScope()->getDescriptionForScope($scope);
        }

        return $this->render('oauth2/service/authorize.html.twig', array(
            'request' => $this->getOAuth2Request()->query->all(),
            'error' => $error,
            'scopes' => $scopes,
        ));
    }

    public function tokenAction(Request $request)
    {
        $this->getOAuth2Server()->addGrantType($this->getOAuth2GrantTypeAuthorizationCode());

        return $this->getOAuth2Server()->handleTokenRequest($this->getOAuth2Request(), $this->getOAuth2Response());
    }

    public function verifyAction()
    {
        $server = $this->getOAuth2Server();

        if (!$server->verifyResourceRequest($this->getOAuth2Request(), $this->getOAuth2Response())) {
            return $server->getResponse();
        }

        $tokenData = $server->getAccessTokenData($this->getOAuth2Request(), $this->getOAuth2Response());

        return $this->createJsonResponse($tokenData);
    }

    public function getUserInfoAction(Request $request, $userId)
    {
        $server = $this->getOAuth2Server();

        if (!$server->verifyResourceRequest($this->getOAuth2Request(), $this->getOAuth2Response())) {
            return $server->getResponse();
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            $this->createJsonResponse(array('error' => 'user is not exsit'));
        }

        $user['smallAvatar'] = $this->getFileUrl($user['smallAvatar']);
        $user['mediumAvatar'] = $this->getFileUrl($user['mediumAvatar']);
        $user['largeAvatar'] = $this->getFileUrl($user['largeAvatar']);

        $result = ArrayToolkit::parts($user, array(
            'nickname', 'verifiedMobile', 'about', 'email', 'title', 'roles', 'smallAvatar', 'mediumAvatar', 'largeAvatar',
        ));

        return $this->createJsonResponse($result);
    }

    protected function handleAuthorizeRequest(Request $request)
    {
        $username = $request->request->get('_username');
        $password = $request->request->get('_password');

        $user = $this->getUserService()->getUserByLoginField($username);

        if (empty($user)) {
            throw $this->createAccessDeniedException('用户不存在');
        }

        if (!$this->getUserService()->verifyPassword($user['id'], $password)) {
            throw $this->createAccessDeniedException('认证失败');
        }

        $this->authenticateUser($user);
        $user = $this->getCurrentUser();

        return $this->getOAuth2Server()->handleAuthorizeRequest($this->getOAuth2Request(), $this->getOAuth2Response(), true, $user['id']);
    }

    protected function getFileUrl($path)
    {
        if (empty($path)) {
            return '';
        }
        if (false !== strpos($path, 'http://')) {
            return $path;
        }
        $path = str_replace('public://', '', $path);
        $path = str_replace('files/', '', $path);
        $path = "/files/{$path}";

        return $path;
    }

    private function authCookie($request)
    {
        $cookies = $request->cookies;
        if ($cookies->has('APP-AUTH-TOKEN')) {
            $token = $cookies->get('APP-AUTH-TOKEN');
            $token = $this->getUserService()->getToken('mobile_login', $token);
            if (empty($token['userId'])) {
                return;
            }
            $user = $this->getUserService()->getUser($token['userId']);

            if ($user) {
                $user['currentIp'] = $request->getClientIp();
            } else {
                return;
            }
            $this->setCurrentUser($user);
        }
    }

    private function setCurrentUser($user)
    {
        $currentUser = new CurrentUser();

        if (empty($user)) {
            $user = array(
                'id' => 0,
                'nickname' => '游客',
                'currentIp' => '',
                'roles' => array(),
            );
        }

        $currentUser->fromArray($user);
        $biz = $this->getBiz();

        $biz['user'] = $currentUser;
    }

    protected function getOAuth2GrantTypeAuthorizationCode()
    {
        return $this->get('oauth2.grant_type.authorization_code');
    }

    protected function getOAuth2StorageScope()
    {
        return $this->get('oauth2.storage.scope');
    }

    protected function getOAuth2Request()
    {
        return $this->get('oauth2.request');
    }

    protected function getOAuth2Response()
    {
        return $this->get('oauth2.response');
    }

    protected function getOAuth2Server()
    {
        return $this->get('oauth2.server');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
