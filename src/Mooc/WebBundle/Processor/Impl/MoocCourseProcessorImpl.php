<?php
namespace Mooc\WebBundle\Processor\Impl;

use Topxia\MobileBundleV2\Processor\Impl\CourseProcessorImpl;

class MoocCourseProcessorImpl extends CourseProcessorImpl
{
    public function getCourse() {
        return parent::getCourse();
    }

    public function searchCourse()
    {
    	$type = $this->getParam("type", '');
    	if (empty($type)) {
    		$this->setParam("type", "");
    	}
    	
    	return parent::searchCourse();
    }
}