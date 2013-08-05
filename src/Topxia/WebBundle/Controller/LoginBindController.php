<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\Component\OAuthClient\OAuthClientFactory;

use Topxia\WebBundle\Form\UserBindNewType;
use Topxia\WebBundle\Form\UserBindExistType;

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
                $this->setFlashMessage('绑定的用户不存在，请重新绑定。');
                return $this->redirect($this->generateUrl('register'));
            }
            $this->authenticateUser($user);
            return $this->redirect($this->generateUrl('homepage'));
        } else {
            $request->getSession()->set('oauth_token', $token);
            return $this->redirect($this->generateUrl('login_bind_new', array('type'  => $type)));
        }

    }

    public function newAction(Request $request, $type)
    {
        $token = $request->getSession()->get('oauth_token');
        $client = $this->createOAuthClient($type);
        $oauthUser = $client->getUserInfo($token);
        $form = $this->createForm(new UserBindNewType(), array(
            'nickname' => $oauthUser['name'],
        ));
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $registration = $form->getData();
                $registration['token'] = $token;
                $user = $this->getUserService()->register($registration, $type);
                $this->authenticateUser($user);
                return $this->redirect($this->generateUrl('homepage'));
            }
        }
        return $this->render('TopxiaWebBundle:Login:bind-new.html.twig', array(
            'form' => $form->createView(),
            'client' => $client,
        ));

    }

    public function existAction(Request $request, $type)
    {
        $token = $request->getSession()->get('oauth_token');
        $client = $this->createOAuthClient($type);

        $oauthUser = $client->getUserInfo($token);
        $error = null;
        $form = $this->createForm(new UserBindExistType());
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $user = $this->getUserService()->getUserByEmail($data['email']);
                if (empty($user)) {
                    $error = '该Email地址尚未注册';
                    goto response;
                }

                if (!$this->getUserService()->verifyPassword($user['id'], $data['password'])) {
                    $error = '密码不正确，请重试！';
                    goto response;
                }

                $bindUser = $this->getUserService()->getUserBindByTypeAndUserId($type, $user['id']);
                if ($bindUser) {
                    $error = "该{{ $this->setting('site.name') }}帐号已经绑定了该第三方网站帐号，如需重新绑定，请先到账户设置中取消绑定！";
                    goto response;
                }
                $this->getUserService()->bindUser($type, $oauthUser['id'], $user['id'], $token);
                $this->authenticateUser($user);
                return $this->redirect($this->generateUrl('homepage'));
            }
        }

        response:
        return $this->render('TopxiaWebBundle:Login:bind-exist.html.twig', array(
            'form' => $form->createView(),
            'error' => $error,
            'client' => $client
        ));
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