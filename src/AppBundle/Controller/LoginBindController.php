<?php

namespace AppBundle\Controller;

use ApiBundle\Api\Resource\Setting\Setting;
use AppBundle\Controller\OAuth2\OAuthUser;
use Biz\Sensitive\Service\SensitiveService;
use Biz\System\SettingException;
use Biz\User\Service\AuthService;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;
use AppBundle\Common\SimpleValidator;
use Biz\User\TokenException;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Component\OAuthClient\OAuthClientFactory;

class LoginBindController extends BaseController
{
    public function indexAction(Request $request, $type)
    {
        if ($request->query->has('_target_path')) {
            $targetPath = $request->query->get('_target_path');
            if ('' == $targetPath) {
                $targetPath = $this->generateUrl('homepage');
            }
            if (!in_array($targetPath, $this->getBlacklist())) {
                $request->getSession()->set('_target_path', $targetPath);
            }
        }
        $client = $this->createOAuthClient($type);

        $token = $this->getTokenService()->makeToken('login.bind', array(
            'data' => array(
                'type' => $type,
                'sessionId' => $request->getSession()->getId(),
            ),
            'times' => $this->isAndroidAndWechat($request) ? 0 : 1,
            'duration' => 3600,
        ));
        $params = array(
            'type' => $type,
            'token' => $token['token'],
        );

        if ($request->query->get('os')) {
            $params['os'] = $request->query->get('os');
        }

        $callbackUrl = $this->generateUrl('login_bind_callback', $params, true);

        $url = $client->getAuthorizeUrl($callbackUrl);

        return $this->redirect($url);
    }

    protected function isAndroidAndWechat($request)
    {
        $userAgent = $this->getWebExtension()->parseUserAgent($request->headers->get('User-Agent'));
        if (!empty($userAgent)
            && !empty($userAgent['os']) && 'Android' == $userAgent['os']['name']
            && !empty($userAgent['client']) && 'WeChat' == $userAgent['client']['name']) {
            return true;
        }

        return false;
    }

    protected function getBlacklist()
    {
        return array('/partner/logout');
    }

    public function callbackAction(Request $request, $type)
    {
        $code = $request->query->get('code');
        $token = $request->query->get('token', '');
        $os = $request->query->get('os', '');
        $this->validateToken($request, $type);
        $callbackParams = array(
            'type' => $type,
            'token' => $token,
        );

        if ($os) {
            $callbackParams['os'] = $os;
        }

        $callbackUrl = $this->generateUrl('login_bind_callback', $callbackParams, true);
        $oauthClient = $this->createOAuthClient($type);
        $token = $oauthClient->getAccessToken($code, $callbackUrl);

        $bind = $this->getUserService()->getUserBindByTypeAndFromId($type, $token['userId']);

        $request->getSession()->set('oauth_token', $token);

        if ($bind) {
            $user = $this->getUserService()->getUser($bind['toId']);

            if (empty($user)) {
                $this->setFlashMessage('danger', 'user.bind.bind_user_not_exist');

                return $this->redirect($this->generateUrl('register'));
            }

            if ($this->getCurrentUser()->getId() != $user['id']) {
                $this->authenticateUser($user);
            }

            if ($this->getAuthService()->hasPartnerAuth()) {
                return $this->redirect($this->generateUrl('partner_login', array('goto' => $this->getTargetPath($request))));
            } else {
                $currentUser = $this->getCurrentUser();
                if (!$currentUser['passwordInit']) {
                    $params = array('goto' => $this->getTargetPath($request));
                    $url = $this->generateUrl('password_init');
                    $goto = $url.'?'.http_build_query($params);
                } else {
                    $goto = $this->getTargetPath($request);
                }

                return $this->redirect($goto);
            }
        } else {
            $oUser = $oauthClient->getUserInfo($token);
            $this->storeOauthUserToSession($request, $oUser, $type, $os);

            return $this->redirect($this->generateUrl('oauth2_login_index'));
        }
    }

    protected function storeOauthUserToSession(Request $request, $oUser, $type, $os = '')
    {
        $setting = new Setting($this->container, $this->getBiz());
        $registerSetting = $setting->getRegister();
        $oauthUser = new OAuthUser();
        $oauthUser->authid = $oUser['id'];
        $oauthUser->name = $this->filterNickname($oUser['name']);
        $oauthUser->avatar = $oUser['avatar'];
        $oauthUser->type = $type;
        $oauthUser->mode = $registerSetting['mode'];
        $oauthUser->captchaEnabled = $registerSetting['captchaEnabled'];
        $oauthUser->os = $os;
        $request->getSession()->set(OAuthUser::SESSION_KEY, $oauthUser);
    }

    protected function filterNickname($nickname)
    {
        $str = mb_substr($nickname, 0, strlen($nickname), 'utf8');
        $filterArr = array_filter(preg_split('/(?<!^)(?!$)/u', $str), function ($item) {
            return preg_match('/^[\x{4e00}-\x{9fa5}a-zA-z0-9_.·]+$/u', $item) ? $item : '';
        });

        return mb_substr(implode('', $filterArr), 0, 9);
    }

    protected function validateToken(Request $request, $type)
    {
        $token = $request->query->get('token', '');
        if (empty($token)) {
            $this->createNewException(TokenException::TOKEN_INVALID());
        }

        $token = $this->getTokenService()->verifyToken('login.bind', $token);
        $tokenData = $token['data'];
        if ($tokenData['type'] != $type) {
            $this->createNewException(TokenException::TOKEN_INVALID());
        }

        if ($tokenData['sessionId'] != $request->getSession()->getId()) {
            $this->createNewException(TokenException::TOKEN_INVALID());
        }
    }

    protected function generateUser($type, $token, $oauthUser, $setData)
    {
        $registration = array();
        $randString = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $oauthUser['name'] = preg_replace('/[^\x{4e00}-\x{9fa5}a-zA-z0-9_.]+/u', '', $oauthUser['name']);
        $oauthUser['name'] = str_replace(array('-'), array('_'), $oauthUser['name']);

        if (!SimpleValidator::nickname($oauthUser['name'])) {
            $oauthUser['name'] = '';
        }

        $tempType = $type;

        if (empty($oauthUser['name'])) {
            if ('weixinmob' == $type || 'weixinweb' == $type) {
                $tempType = 'weixin';
            }

            $oauthUser['name'] = $tempType.substr($randString, 9, 3);
        }

        $nameLength = mb_strlen($oauthUser['name'], 'utf-8');

        if ($nameLength > 10) {
            $oauthUser['name'] = mb_substr($oauthUser['name'], 0, 11, 'utf-8');
        }

        if (!empty($setData['nickname']) && !empty($setData['email'])) {
            $registration['nickname'] = $setData['nickname'];
            $registration['email'] = $setData['email'];
            $registration['emailOrMobile'] = $setData['email'];
        } else {
            $nicknames = array();
            $nicknames[] = isset($setData['nickname']) ? $setData['nickname'] : $oauthUser['name'];
            $nicknames[] = mb_substr($oauthUser['name'], 0, 8, 'utf-8').substr($randString, 0, 3);
            $nicknames[] = mb_substr($oauthUser['name'], 0, 8, 'utf-8').substr($randString, 3, 3);
            $nicknames[] = mb_substr($oauthUser['name'], 0, 8, 'utf-8').substr($randString, 6, 3);

            foreach ($nicknames as $name) {
                if ($this->getUserService()->isNicknameAvaliable($name)) {
                    $registration['nickname'] = $name;
                    break;
                }
            }

            if (empty($registration['nickname'])) {
                return array();
            }

            $registration['email'] = 'u_'.substr($randString, 0, 12).'@edusoho.net';
        }

        if ($this->getSensitiveService()->scanText($registration['nickname'])) {
            return $this->createMessageResponse('error', '用户名中含有敏感词！');
        }

        $registration['password'] = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 8);
        $registration['token'] = $token;
        $registration['createdIp'] = $oauthUser['createdIp'];

        if (isset($setData['mobile']) && !empty($setData['mobile'])) {
            $registration['mobile'] = $setData['mobile'];
            $registration['emailOrMobile'] = $setData['mobile'];
        }

        if (isset($setData['invite_code']) && !empty($setData['invite_code'])) {
            $registration['invite_code'] = $setData['invite_code'];
        }

        $user = $this->getAuthService()->register($registration, $type);

        return $user;
    }

    protected function createOAuthClient($type)
    {
        $settings = $this->setting('login_bind');

        if (empty($settings)) {
            $this->createNewException(SettingException::NOTFOUND_THIRD_PARTY_AUTH_CONFIG());
        }

        if (empty($settings) || !isset($settings[$type.'_enabled']) || empty($settings[$type.'_key']) || empty($settings[$type.'_secret'])) {
            $this->createNewException(SettingException::NOTFOUND_THIRD_PARTY_AUTH_CONFIG());
        }

        if (!$settings[$type.'_enabled']) {
            $this->createNewException(SettingException::FORBIDDEN_THIRD_PARTY_AUTH());
        }

        $config = array('key' => $settings[$type.'_key'], 'secret' => $settings[$type.'_secret']);

        $client = OAuthClientFactory::create($type, $config);

        return $client;
    }

    /**
     * @return SensitiveService
     */
    protected function getSensitiveService()
    {
        return $this->getBiz()->service('Sensitive:SensitiveService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->getBiz()->service('User:AuthService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }
}
