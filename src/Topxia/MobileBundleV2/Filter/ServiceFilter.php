<?php
namespace Topxia\MobileBundleV2\Filter;
use  Topxia\MobileBundleV2\Filter\Filter;
use  Topxia\MobileBundleV2\Filter\FilterResult;
class ServiceFilter extends Filter
{
	public $filterUrl = "/.+/";

	public function invoke()
	{
		return $this->next();
	}
}