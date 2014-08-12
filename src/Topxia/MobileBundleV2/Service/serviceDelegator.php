<?php
namespace Topxia\MobileBundleV2\Service;

class serviceDelegator
{
	private $target;
	function __construct($target){
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

		call_user_func(array($this->target, "after"), $arguments);
		$functionResult = call_user_func(array($this->target, $name), $arguments);
		call_user_func(array($this->target, "before"), $arguments);
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