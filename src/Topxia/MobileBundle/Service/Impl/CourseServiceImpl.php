<?php
namespace Topxia\MobileBundle\Service\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\MobileBundle\Service\CourseService;

class CourseServiceImpl extends BaseService implements CourseService
{
	private $formData;
	function __construct($formData){
	        $this->formData = $formData;
	}

	public function getVersion()
	{
		return $this->formData;
	}
}