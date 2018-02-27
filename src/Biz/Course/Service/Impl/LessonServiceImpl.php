<?php

namespace Biz\Course\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Course\Service\LessonService;

class LessonServiceImpl extends BaseService implements LessonService
{
    public function countLessons($courseId)
    {
        return $this->getCourseChapterDao()->count(array('type' => 'lesson', 'courseId' => $courseId));
    }

    protected function getCourseChapterDao()
    {
        return $this->createDao('Course:CourseChapterDao');
    }
}
