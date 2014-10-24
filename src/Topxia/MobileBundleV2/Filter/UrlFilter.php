<?php
namespace Topxia\MobileBundleV2\Filter;
use  Topxia\MobileBundleV2\Filter\Filter;
use  Topxia\MobileBundleV2\Filter\FilterResult;
class UrlFilter extends Filter
{
	public $filterUrl = "/Course\\/getVersion/";

	public function invoke()
	{
		return $this->next();
	}
}