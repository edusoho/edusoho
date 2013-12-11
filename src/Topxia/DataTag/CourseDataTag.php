<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class CourseDataTag extends BaseDataTag implements DataTag  
{
    public function getData($arguments)
    {
    	return $this->getCoursService()->getCourse($arguments);
    }

    protected function getCoursService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}