<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Course\Service\LessonService;
use Codeages\Biz\Framework\Event\Event;

class LessonServiceImpl extends BaseService implements LessonService
{
    public function countLessons($courseId)
    {
        return $this->getCourseChapterDao()->count(array('type' => 'lesson', 'courseId' => $courseId));
    }

    public function publishLesson($lessonId)
    {
        $chapter = $this->getCourseChapterDao()->get($lessonId);
        if (empty($chapter) || $chapter['type'] != 'lesson') {
            throw $this->createInvalidArgumentException('Argument Invalid');
        }

        $lesson = $this->getCourseChapterDao()->update($lessonId, array('status' => 'published'));

        $this->dispatchEvent('course.lesson.publish', new Event($lesson));

        return $lesson;
    }

    public function publishLessonByCourseId($courseId)
    {
        $chapters = $this->getCourseChapterDao()->findLessonsByCourseId($courseId);

        if (empty($chapters)) {
            return;
        }

        foreach ($chapters as $chapter) {
            $this->publishLesson($chapter['id']);
        }
    }

    public function unpublishLesson($lessonId)
    {
        $chapter = $this->getCourseChapterDao()->get($lessonId);

        if (empty($chapter) || $chapter['type'] != 'lesson') {
            throw $this->createInvalidArgumentException('Argument Invalid');
        }

        $lesson = $this->getCourseChapterDao()->update($lessonId, array('status' => 'unpublished'));

        $this->dispatchEvent('course.lesson.unpublish', new Event($lesson));

        return $lesson;
    }

    public function deleteLesson($lessonId)
    {
        $lesson = $this->getCourseChapterDao()->get($lessonId);

        if (empty($lesson)) {
            return;
        }

        if ($lesson['type'] != 'lesson') {
            throw $this->createInvalidArgumentException('Argument Invalid');
        }

        $this->getCourseChapterDao()->delete($lesson['id']);
        $this->getTaskService()->deleteTasksByCategoryId($lesson['courseId'], $lesson['id']);

        $this->dispatchEvent('course.lesson.delete', new Event($lesson));

        $this->getLogService()->info('course', 'delete_lesson', "删除课时(#{$lessonId})", $lesson);

        return true;
    }

    protected function getCourseChapterDao()
    {
        return $this->createDao('Course:CourseChapterDao');
    }

    protected function getTaskService()
    {
        return $this->createDao('Task:TaskService');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
