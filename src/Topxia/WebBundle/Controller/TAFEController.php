<?php
namespace Topxia\WebBundle\Controller;

class TAFEController extends BaseController
{
   public function indexAction()
    {
        return $this->render('TopxiaWebBundle:TAFE:index.html.twig');
    }
}