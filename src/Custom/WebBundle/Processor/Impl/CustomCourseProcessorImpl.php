<?php
namespace Custom\WebBundle\Processor\Impl;

use Topxia\MobileBundleV2\Processor\Impl\CourseProcessorImpl;

class CustomCourseProcessorImpl extends CourseProcessorImpl
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