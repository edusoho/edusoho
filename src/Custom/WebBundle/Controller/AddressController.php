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
				$shippingAddress = $this->getShippingAddressService()->addShippingAddress($fields);
				return $this->render('CustomWebBundle:Address:default-shipping-address.html.twig', array(
					'shippingAddress' => $shippingAddress
				));
			} else {
				$shippingAddress = $this->getShippingAddressService()->updateShippingAddress($id, $fields);
				return $this->render('CustomWebBundle:Address:default-shipping-address.html.twig', array(
					'shippingAddress' => $shippingAddress
				));
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