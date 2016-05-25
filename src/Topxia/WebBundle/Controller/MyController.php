<?php
namespace Topxia\WebBundle\Controller;

class MyController extends BaseController
{
    public function avatarAlertAction()
    {
        return $this->render('TopxiaWebBundle:My:avatar-alert.html.twig');
    }

}
