<?php
namespace Topxia\MobileBundleV2\Service;

class BaseService
{
	public $formData;
	public $controller;
	public $request;
	protected $delegator;

	private function __construct($controller){
		$this->controller = $controller;
		$this->request = $controller->request;
		$this->formData = $controller->formData;
	}

	public static function getInstance($class, $controller)
	{
		$instance = new $class($controller);
		$serviceDelegator = new serviceDelegator($instance);
		$instance->setDelegator($serviceDelegator);

		return $serviceDelegator;
	}

	protected function getParam($name, $default = null)
	{
		$result = $this->request->request->get($name);
        		return $result ? $result : $default;
	}

	public function setDelegator($serviceDelegator)
	{
		$this->delegator = $serviceDelegator;
	}

	public function getDelegator()
	{
		return $this->delegator;
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