<?php
namespace Custom\Service\Course\Impl;

use Custom\Service\Course\CourseService;
use Topxia\Service\Course\Impl\CourseServiceImpl as BaseCourseServiceImpl;

class CourseServiceImpl extends BaseCourseServiceImpl implements CourseService
{
    public function findRecentLiveLesson($count)
    {
        return $this->getCustomLessonDao()->findRecentLiveLesson($count);
    }

    private function getCustomLessonDao()
    {
        return $this->createDao('Custom:Course.CustomLessonDao');
    }

}
