<?php
namespace Topxia\MobileBundle\Filter;
use  Topxia\MobileBundle\Filter\Filter;
use  Topxia\MobileBundle\Filter\FilterResult;
class UrlFilter extends Filter
{
	public $filterUrl = "/Course\\/getVersion/";

	public function invoke()
	{
		return new FilterResult(true, true, array("ddd"=>11));
	}
}