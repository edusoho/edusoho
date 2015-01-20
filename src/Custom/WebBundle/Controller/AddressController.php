<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class AddressController extends BaseController
{
	public function showAddressAction(Request $request, $action)
	{

		return $this->render('CustomWebBundle:Address:address-modal.html.twig', array(
			'action' => $action
		));
	}
}