<?php
namespace Topxia\MobileBundle\Service\Impl;

use Topxia\MobileBundle\Service\BaseService;
use Topxia\MobileBundle\Service\CourseService;

class CourseServiceImpl extends BaseService implements CourseService
{
	public function getVersion()
	{
		var_dump("CourseServiceImpl->getVersion");
		return $this->formData;
	}

	public function after(){
		var_dump("CourseServiceImpl->after");
	}

	public function before(){
		var_dump("CourseServiceImpl->before");
	}
}