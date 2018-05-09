<?php

namespace AppBundle\Controller\Admin;

use Biz\CloudPlatform\CloudAPIFactory;
use Symfony\Component\HttpFoundation\Request;

class WeChatAppController extends BaseController
{
    public function indexAction()
    {
        $mpSdk = $this->getMpService()->getMpSdk();
        if ($mpSdk->getCurrentMpRequest()) {
            return $this->forward('admin_wechat_app_request_success', array());
        }

        return $this->render('admin/wechat-app/index.html.twig', array(
            'isNoneLevel' => $this->isNoneLevel(),
        ));
    }

    public function requestAction(Request $request)
    {
        if ($this->isNoneLevel()) {
            return $this->render('admin/wechat-app/request-fail.html.twig', array());
        }

        $mpSdk = $this->getMpService()->getMpSdk();
        if ($mpSdk->getCurrentMpRequest()) {
            return $this->forward('admin_wechat_app_request_success', array());
        }

        if ($request->isMethod('POST')) {
            $fields = $request->request->all();
            $mpSdk->sendMpRequest($fields);

            return $this->redirect($this->generateUrl('admin_wechat_app_request'));
        }

        return $this->render('admin/wechat-app/request.html.twig', array());
    }

    protected function isNoneLevel()
    {
        $me = $this->createApi()->get('/me');
        return empty($me['level']) || $me['level'] == 'none';
    }

    protected function createApi($node = 'root')
    {
        return CloudAPIFactory::create($node);
    }

    public function requestSuccessAction()
    {
        return $this->render('admin/wechat-app/request-success.html.twig', array());
    }

    public function getMpService()
    {
        return $this->createService('Mp:MpService');
    }
}
