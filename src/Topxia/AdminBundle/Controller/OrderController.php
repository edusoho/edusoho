<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class OrderController extends BaseController
{
	public function indexAction(Request $request)
	{
		return $this->render('TopxiaAdminBundle:Order:index.html.twig', array(
		));
	}
}