<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController;


class DefaultController extends BaseController
{
	public function indexAction()
	{
         return $this->render('TopxiaWebBundle:Default:test.html.twig');
	}
}