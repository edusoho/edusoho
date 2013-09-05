<?php
namespace Topxia\WebBundle\Controller;

class InstallController extends BaseController
{
    public function indexAction()
    {
    	return $this->render('TopxiaWebBundle:Install:index.html.twig');
    }
}