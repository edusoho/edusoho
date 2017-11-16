<?php

namespace AppBundle\Controller\OAuth2;

use ApiBundle\Api\Resource\Setting\Setting;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LoginController extends BaseController
{
    public function indexAction(Request $request)
    {
        $setting = new Setting($this->container, $this->getBiz());

        $registerSetting = $setting->getRegister();

        $oauthUser = $this->getOauthUser($request);

        return $this->render('oauth2/index.html.twig', array(
            'mode' => $registerSetting['mode'],
            'oauthUser' => $oauthUser,
        ));
    }

    public function bindAction(Request $request)
    {

        $oauthUser = $this->getOauthUser($request);

        $type = $request->query->get('type');
        $account = $request->query->get('account');

        $user = $this->getUserByTypeAndAccount($type, $account);

        if ($user) {
            return $this->render(
                'oauth2/bind-account.html.twig', array(
                'oauthUser' => $oauthUser,
                'esUser' => $user,
                'account' => $account,
                'type' => $type,
            ));
        } else {
            return $this->render(
                'oauth2/create-account.html.twig', array(
                'oauthUser' => $oauthUser,
                'esUser' => $user,
                'account' => $account,
                'type' => $type,
            ));
        }
    }

    public function successAction(Request $request)
    {
        return $this->render('oauth2/success.html.twig', array(
        ));
    }

    public function createAction(Request $request)
    {
        return $this->render('oauth2/create-accout.html.twig', array(
        ));
    }

    private function getUserByTypeAndAccount($type, $account)
    {
        $user = null;
        switch ($type) {
            case 'email':
                $user = $this->getUserService()->getUserByEmail($account);
                break;
            case 'mobile':
                $user = $this->getUserService()->getUserByVerifiedMobile($account);
                break;
            default:
                throw new NotFoundHttpException();
        }

        return $user;
    }

    private function getOauthUser(Request $request)
    {
        $oauthUser = $request->getSession()->get('oauth_user');
        if (!$oauthUser) {
            throw new NotFoundHttpException();
        }

        return $oauthUser;
    }
}
