<?php
namespace Topxia\MobileBundle\Filter;

class FilterResult
{
	private $isNext;
	private $isFilter;
	public $resultData;

	function __construct($isNext, $isFilter, $resultData){
	        $this->isNext = $isNext;
	        $this->isFilter = $isFilter;
	        $this->resultData = $resultData;
	}

	public function hasNext()
	{
		return $this->isNext;
	}

	public function hasFilter()
	{
		return $this->isFilter;
	}
}