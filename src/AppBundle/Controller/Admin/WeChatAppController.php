<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;

class WeChatAppController extends BaseController
{
    public function indexAction()
    {
        $mpSdk = $this->getMpService()->getMpSdk();
        if ($mpSdk->getCurrentMpRequest()) {
            return $this->redirect($this->generateUrl('admin_wechat_app_request_success'));
        }
        return $this->render('admin/wechat-app/index.html.twig', array());
    }

    public function requestAction(Request $request)
    {
        $mpSdk = $this->getMpService()->getMpSdk();
        if ($mpSdk->getCurrentMpRequest()) {
            return $this->redirect($this->generateUrl('admin_wechat_app_request_success'));
        }
        if ($request->isMethod('POST')) {
            $fields = $request->request->all();
            $mpSdk->sendMpRequest($fields);

            return $this->redirect($this->generateUrl('admin_wechat_app_request'));
        }

        return $this->render('admin/wechat-app/request.html.twig', array());
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
