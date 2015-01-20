<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class AddressController extends BaseController
{
	public function showShippingAddressAction(Request $request, $id)
	{
		if ($request->getMethod() == 'POST') {
			$fields = $request->request->all();
			$fields['telNo'] = implode('-', $fields['telNo']);
			if(empty($id)) {
				$this->getShippingAddressService()->addShippingAddress($fields);
				return $this->createJsonResponse(true);
			} else {
				$this->getShippingAddress()->updateShippingAddress($id, $fields);
				return $this->createJsonResponse(true);
			}
		}

		$shippingAddress = $this->getShippingAddressService()->getShippingAddress($id);
		return $this->render('CustomWebBundle:Address:shipping-address-modal.html.twig', array(
			'shippingAddress' => $shippingAddress
		));
	}

	private function getShippingAddressService()
	{
		return $this->getServiceKernel()->createService('Custom:Address.ShippingAddressService');
	}

}