<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController;


class TimelineController extends BaseController
{
	public function indexAction()
	{
         return $this->render('CustomWebBundle:Timeline:index.html.twig');
	}
}