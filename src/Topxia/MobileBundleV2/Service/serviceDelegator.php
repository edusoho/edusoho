<?php
namespace Topxia\MobileBundleV2\Service;

class serviceDelegator
{
	private $target;
	private $invokeArray;

	function __construct($target){
		$this->invokeArray = array();
		$this->target = $target;
	}

	function __call($name, $arguments)
	{
		if (!method_exists($this->target, $name)) {
			return array("error" => "method not exists");
		}
		if (method_exists($this, $name) || $this->filterMethod($name)) {
			return array("error" => "the method is serviceDelegator");
		}

		return $this->invokeFunction($name, $arguments);
		return $functionResult;
	}

	public function stopInvoke()
	{
		$this->invokeArray = array();
	}

	private function invokeFunction($name, $arguments)
	{
		$functionResult = array();
		array_push($this->invokeArray, 'before', $name, 'after');
		while ($function = array_pop($this->invokeArray)) {
			$functionResult = call_user_func(array($this->target, $function), $arguments);
		}

		return $functionResult;
	}

	private $methodFilters = array(
		"__construct",
		"after",
		"before"
	);

	private function filterMethod($method)
	{
		return in_array($method, $this->methodFilters);
	}

}