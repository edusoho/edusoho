<?php

namespace Fomalhaut\WechatBundle\Controller;

use Topxia\WebBundle\Controller\BaseController;

class DefaultController extends BaseController
{
    public function indexAction()
    {
        $sdk = $this->get('fomalhaut_wechat.sdk.wechat');

        $curuser = $this->getCurrentUser();
        return $this->render('WechatBundle:Default:index.html.twig', array('name' => $curuser['nickname']));
    }
}
