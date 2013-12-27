<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class MoneyController extends BaseController
{
	public function recordsAction(Request $request)
	{
		return $this->render('TopxiaAdminBundle:Money:records.html.twig', array(
		));
	}
}