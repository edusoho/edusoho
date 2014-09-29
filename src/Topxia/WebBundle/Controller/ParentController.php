<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ParentController extends BaseController
{
	function childStatusAction(Request $request)
	{
		return $this->render('TopxiaWebBundle:Parent:child-status.html.twig');
	}
}