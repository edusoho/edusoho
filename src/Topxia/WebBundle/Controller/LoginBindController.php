<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\Component\OAuthClient\OAuthClientFactory;

class LoginBindController extends BaseController
{

    public function indexAction (Request $request, $type)
    {

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
            return $this->redirect($this->generateUrl('homepage'));
        } else {
            $request->getSession()->set('oauth_token', $token);
            return $this->redirect($this->generateUrl('login_bind_choose', array('type'  => $type)));
        }

    }

    public function chooseAction(Request $request, $type)
    {
        $token = $request->getSession()->get('oauth_token');
        $client = $this->createOAuthClient($type);
        $oauthUser = $client->getUserInfo($token);

        return $this->render('TopxiaWebBundle:Login:bind-choose.html.twig', array(
            'oauthUser' => $oauthUser,
            'client' => $client,
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

        if (empty($oauthUser)) {
            $response = array('success' => false, 'message' => '网络超时，获取用户信息失败，请重试。');
            goto response;
        }

        $user = $this->generateUser($type, $token, $oauthUser);
        if (empty($user)) {
            $response = array('success' => false, 'message' => '登录失败，请重试！');
            goto response;
        }

        $this->authenticateUser($user);
        $response = array('success' => true);

        response:
        return $this->createJsonResponse($response);
    }

    private function generateUser($type, $token, $oauthUser)
    {
        $registration = array();

        $randString = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

        // var_dump($oauthUser['name']);exit();

        // mb_strlen($oauthUser['name'])



        $nicknames = array();
        $nicknames[] = $oauthUser['name'];
        $nicknames[] = $oauthUser['name'] . '_' . substr($randString, 0, 3);
        $nicknames[] = $oauthUser['name'] . '_' . substr($randString, 3, 3);
        $nicknames[] = $oauthUser['name'] . '_' . substr($randString, 6, 3);

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
        $registration['token'] = $token;

        $user = $this->getUserService()->register($registration, $type);

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
            $response = array('success' => true);
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

}