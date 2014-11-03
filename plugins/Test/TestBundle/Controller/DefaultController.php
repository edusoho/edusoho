<?php

namespace Test\TestBundle\Controller;

use Topxia\WebBundle\Controller\BaseController;

class DefaultController extends BaseController
{
    public function indexAction()
    {
        //$sdk = $this->get('fomalhaut_wechat.sdk.wechat');
        $curuser = $this->getCurrentUser();
        $name = $curuser['nickname'];
        return $this->render('TestBundle:Default:index.html.twig', array('name' => $name));
    }
}
