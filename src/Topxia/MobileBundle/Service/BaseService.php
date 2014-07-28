<?php
namespace Topxia\MobileBundle\Service;

class BaseService
{
	public $formData;
	private function __construct($formData){
		$this->formData = $formData;
	}

	public static function getInstance($class, $formData)
	{
		$instance = new $class($formData);
		$serviceDelegator = new serviceDelegator($instance);
		return $serviceDelegator;
	}

	public function after(){
	}

	public function before(){
	}
}