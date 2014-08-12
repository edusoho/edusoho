<?php
namespace Topxia\MobileBundleV2\Service;

class BaseService
{
	public $formData;
	public $controller;
	public $request;

	private function __construct($controller){
		$this->controller = $controller;
		$this->request = $controller->request;
		$this->formData = $controller->formData;
	}

	public static function getInstance($class, $controller)
	{
		$instance = new $class($controller);
		$serviceDelegator = new serviceDelegator($instance);
		return $serviceDelegator;
	}

	public function after(){
	}

	public function before(){
	}

	public function createErrorResponse($name, $message)
	{
	    $error = array('error' => array('name' => $name, 'message' => $message));
	    return $error;
	}
}