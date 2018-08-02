<?php

namespace AppBundle\Controller\Admin;

class WeChatAppController extends BaseController
{
    public function indexAction()
    {
        $wechatAppStatus = $this->getWeChatAppService()->getWeChatAppStatus();

        return $this->render('admin/wechat-app/index.html.twig', $wechatAppStatus);
    }

    protected function getWeChatAppService()
    {
        return $this->createService('WeChat:WeChatAppService');
    }
}
