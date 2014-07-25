<?php
namespace Topxia\MobileBundle\Filter;
use  Topxia\MobileBundle\Filter\Filter;
use  Topxia\MobileBundle\Filter\FilterResult;
class ServiceFilter extends Filter
{
	public $filterUrl = "/.+/";

	public function invoke()
	{
		return new FilterResult(true, true, array("ddd"=>22));
	}
}