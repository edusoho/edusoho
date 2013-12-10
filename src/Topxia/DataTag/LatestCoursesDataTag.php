<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class LatestCoursesDataTag implements DataTag
{
    public function getData($arguments)
    {

    }

    protected function getCoursService()
    {
        $this->getServiceKernel()->createService('Course.CourseService');
    }
}