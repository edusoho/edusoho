<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Component\OAuthClient\OAuthClientFactory;

class LoginBindController extends BaseController
{

    public function indexAction (Request $request, $type)
    {
        if ($request->query->has('_target_path')) {
            $request->getSession()->set('_target_path', $request->query->get('_target_path'));
        }

        $client = $this->createOAuthClient($type);
        $callbackUrl = $this->generateUrl('login_bind_callback', array('type' => $type), true);
        $url = $client->getAuthorizeUrl($callbackUrl);

        return $this->redirect($url);
    }

    public function callbackAction(Request $request, $type)
    {
        $code = $request->query->get('code');
        $callbackUrl = $this->generateUrl('login_bind_callback', array('type' => $type), true);
        $token = $this->createOAuthClient($type)->getAccessToken($code, $callbackUrl);
        $bind = $this->getUserService()->getUserBindByTypeAndFromId($type, $token['userId']);
        if ($bind) {
            $user = $this->getUserService()->getUser($bind['toId']);
            if (empty($user)) {
                $this->setFlashMessage('danger','绑定的用户不存在，请重新绑定。');
                return $this->redirect($this->generateUrl('register'));
            }
            $this->authenticateUser($user);
            if ($this->getAuthService()->hasPartnerAuth()) {
                return $this->redirect($this->generateUrl('partner_login', array('goto'=>$request->getSession()->get('_target_path', ''))));
            } else {
                $goto = $request->getSession()->get('_target_path', '') ? : $this->generateUrl('homepage');
                return $this->redirect($goto);
            }
        } else {
            $request->getSession()->set('oauth_token', $token);
            return $this->redirect($this->generateUrl('login_bind_choose', array('type'  => $type)));
        }

    }

    public function chooseAction(Request $request, $type)
    {
        $token = $request->getSession()->get('oauth_token');
        $client = $this->createOAuthClient($type);

        try {
            $oauthUser = $client->getUserInfo($token);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $clientInfo = $client->getClientInfo();
            if ($message == 'unaudited') {
                $message = '抱歉！暂时无法通过第三方帐号登录。原因：'.$clientInfo['name'].'登录连接的审核还未通过。';
            } else {
                $message = '抱歉！暂时无法通过第三方帐号登录。原因：'.$message;
            }
            $this->setFlashMessage('danger', $message);
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('TopxiaWebBundle:Login:bind-choose.html.twig', array(
            'oauthUser' => $oauthUser,
            'client' => $client,
            'hasPartnerAuth' => $this->getAuthService()->hasPartnerAuth(),
        ));
    }

    public function newAction(Request $request, $type)
    {
        $token = $request->getSession()->get('oauth_token');
        if (empty($token)) {
            $response = array('success' => false, 'message' => '页面已过期，请重新登录。');
            goto response;
        }

        $client = $this->createOAuthClient($type);
        $oauthUser = $client->getUserInfo($token);
        $oauthUser['createdIp'] = $request->getClientIp();
        
        if (empty($oauthUser['id'])) {
            $response = array('success' => false, 'message' => '网络超时，获取用户信息失败，请重试。');
            goto response;
        }

        $user = $this->generateUser($type, $token, $oauthUser,$setData=array());
        if (empty($user)) {
            $response = array('success' => false, 'message' => '登录失败，请重试！');
            goto response;
        }

        $this->authenticateUser($user);
        $response = array('success' => true, '_target_path' => $request->getSession()->get('_target_path', $this->generateUrl('homepage')));

        response:
        return $this->createJsonResponse($response);
    }

    public function newSetAction(Request $request, $type)
    {
        $setData = $request->request->all();

        $token = $request->getSession()->get('oauth_token');
        if (empty($token)) {
            $response = array('success' => false, 'message' => '页面已过期，请重新登录。');
            goto response;
        }

        $client = $this->createOAuthClient($type);
        $oauthUser = $client->getUserInfo($token);
        $oauthUser['createdIp'] = $request->getClientIp();
        
        if (empty($oauthUser['id'])) {
            $response = array('success' => false, 'message' => '网络超时，获取用户信息失败，请重试。');
            goto response;
        }

        $user = $this->generateUser($type, $token, $oauthUser,$setData);
        if (empty($user)) {
            $response = array('success' => false, 'message' => '登录失败，请重试！');
            goto response;
        }

        $this->getUserService()->setupAccount($user['id']);
        $this->authenticateUser($user);

        $response = array('success' => true, '_target_path' => $request->getSession()->get('_target_path', $this->generateUrl('homepage')));

        response:
        return $this->createJsonResponse($response);
    }

    private function generateUser($type, $token, $oauthUser,$setData)
    {
        $registration = array();

        $randString = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $oauthUser['name'] = preg_replace('/[^\x{4e00}-\x{9fa5}a-zA-z0-9_.]+/u', '', $oauthUser['name']);
        $oauthUser['name'] = str_replace(array('-'), array('_'), $oauthUser['name']);

        if (empty($oauthUser['name'])) {
            $oauthUser['name'] = "{$type}" . substr($randString, 9, 3);
        }

        $nameLength = mb_strlen($oauthUser['name'], 'utf-8');
        if ($nameLength > 10) {
            $oauthUser['name'] = mb_substr($oauthUser['name'], 0, 11, 'utf-8');
        }

        if (!empty($setData['nickname']) && !empty($setData['email'])) {
            $registration['nickname'] = $setData['nickname'];
            $registration['email'] = $setData['email'];
        } else {
            $nicknames = array();
            $nicknames[] = $oauthUser['name'];
            $nicknames[] = mb_substr($oauthUser['name'], 0, 8, 'utf-8') . substr($randString, 0, 3);
            $nicknames[] = mb_substr($oauthUser['name'], 0, 8, 'utf-8') . substr($randString, 3, 3);
            $nicknames[] = mb_substr($oauthUser['name'], 0, 8, 'utf-8') . substr($randString, 6, 3);

            foreach ($nicknames as $name) {
                if ($this->getUserService()->isNicknameAvaliable($name)) {
                    $registration['nickname'] = $name;
                    break;
                }
            }

            if (empty($registration['nickname'])) {
                return null;
            }

            $registration['email'] = 'u_' . substr($randString, 0, 12) . '@edusoho.net';
        }
        $registration['password'] = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 8);
        $registration['token'] = $token;
        $registration['createdIp'] = $oauthUser['createdIp'];

        $user = $this->getAuthService()->register($registration, $type);
        return $user;
    }

    public function existAction(Request $request, $type)
    {
        $token = $request->getSession()->get('oauth_token');
        $client = $this->createOAuthClient($type);

        $oauthUser = $client->getUserInfo($token);

        $data = $request->request->all();
        $user = $this->getUserService()->getUserByEmail($data['email']);
        if (empty($user)) {
            $response = array('success' => false, 'message' => '该Email地址尚未注册');
        } elseif(!$this->getUserService()->verifyPassword($user['id'], $data['password'])) {
            $response = array('success' => false, 'message' => '密码不正确，请重试！');
        } elseif ($this->getUserService()->getUserBindByTypeAndUserId($type, $user['id'])) {
            $response = array('success' => false, 'message' => "该{{ $this->setting('site.name') }}帐号已经绑定了该第三方网站的其他帐号，如需重新绑定，请先到账户设置中取消绑定！");
        } else {
            $response = array('success' => true, '_target_path' => $request->getSession()->get('_target_path', $this->generateUrl('homepage')));
            $this->getUserService()->bindUser($type, $oauthUser['id'], $user['id'], $token);
            $this->authenticateUser($user);
        }

        return $this->createJsonResponse($response);
    }

    private function createOAuthClient($type)
    {
        $settings = $this->setting('login_bind');        

        if (empty($settings)) {
            throw new \RuntimeException('第三方登录系统参数尚未配置，请先配置。');
        }

        if (empty($settings) or !isset($settings[$type.'_enabled']) or empty($settings[$type.'_key']) or empty($settings[$type.'_secret'])) {
            throw new \RuntimeException("第三方登录({$type})系统参数尚未配置，请先配置。");
        }

        if (!$settings[$type.'_enabled']) {
            throw new \RuntimeException("第三方登录({$type})未开启");
        }

        $config = array('key' => $settings[$type.'_key'], 'secret' => $settings[$type.'_secret']);
        $client = OAuthClientFactory::create($type, $config);

        return $client;
    }

    private function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

}