<?php
namespace Mooc\AdminBundle\Controller;

class BaseController extends \Topxia\AdminBundle\Controller\BaseController {
	protected function checkId($id) {
		if ($id <= 0) {
			throw $this->createNotFoundException();
		}
	}

	protected function createService($name) {
		return $this->getServiceKernel()->createService($name);
	}
}