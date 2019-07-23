<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\LessonException;
use Biz\Course\Service\LessonService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use AppBundle\Common\ArrayToolkit;

class LessonServiceImpl extends BaseService implements LessonService
{
    const LESSON_LIMIT_NUMBER = 300;

    public function getLesson($lessonId)
    {
        $lesson = $this->getCourseChapterDao()->get($lessonId);

        if (empty($lesson) || 'lesson' != $lesson['type']) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        return $lesson;
    }

    public function countLessons($conditions)
    {
        $conditions['type'] = 'lesson';

        return $this->getCourseChapterDao()->count($conditions);
    }

    public function createLesson($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('title', 'fromCourseId'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $this->beginTransaction();
        try {
            $lesson = array(
                'courseId' => $fields['fromCourseId'],
                'title' => $fields['title'],
                'type' => 'lesson',
                'status' => 'created',
            );
            $lesson = $this->getCourseChapterDao()->create($lesson);

            $taskFields = $this->parseTaskFields($fields);
            $taskFields['categoryId'] = $lesson['id'];
            $taskFields['isLesson'] = true;
            $task = $this->getTaskService()->createTask($taskFields);

            $this->commit();

            $this->dispatchEvent('course.lesson.create', new Event($lesson));

            return array($lesson, $task);
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }
    }

    public function updateLesson($lessonId, $fields)
    {
        $chapter = $this->getCourseChapterDao()->get($lessonId);
        $this->getCourseService()->tryManageCourse($chapter['courseId']);

        if (empty($chapter) || 'lesson' != $chapter['type']) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $fields = ArrayToolkit::parts($fields, array('title', 'number', 'seq', 'parentId'));

        $lesson = $this->getCourseChapterDao()->update($chapter['id'], $fields);
        $this->dispatchEvent('course.lesson.update', new Event($lesson));

        return $lesson;
    }

    public function publishLesson($courseId, $lessonId, $updateLessonNum = true)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $chapter = $this->getCourseChapterDao()->get($lessonId);
        if (empty($chapter) || $chapter['courseId'] != $courseId || 'lesson' != $chapter['type']) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $lesson = $this->getCourseChapterDao()->update($lessonId, array('status' => 'published'));
        $this->publishTasks($lesson['id']);

        $this->dispatchEvent('course.lesson.publish', new Event($lesson));
        $this->updateLessonNumbers($courseId);

        return $lesson;
    }

    public function publishLessonByCourseId($courseId)
    {
        $chapters = $this->getCourseChapterDao()->findLessonsByCourseId($courseId);

        if (empty($chapters)) {
            return;
        }

        foreach ($chapters as $chapter) {
            $this->publishLesson($courseId, $chapter['id'], false);
        }

        $this->updateLessonNumbers($courseId);
    }

    public function unpublishLesson($courseId, $lessonId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $chapter = $this->getCourseChapterDao()->get($lessonId);

        if (empty($chapter) || $chapter['courseId'] != $courseId || 'lesson' != $chapter['type']) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $lesson = $this->getCourseChapterDao()->update($lessonId, array('status' => 'unpublished'));
        $this->unpublishTasks($lesson['id']);

        $this->dispatchEvent('course.lesson.unpublish', new Event($lesson));
        $this->updateLessonNumbers($courseId);

        return $lesson;
    }

    public function deleteLesson($courseId, $lessonId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseChapterDao()->get($lessonId);

        if (empty($lesson)) {
            return;
        }

        if ($lesson['courseId'] != $courseId || 'lesson' != $lesson['type']) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $this->getTaskService()->deleteTasksByCategoryId($lesson['courseId'], $lesson['id']);
        $this->getCourseChapterDao()->delete($lesson['id']);

        $this->dispatchEvent('course.lesson.delete', new Event($lesson));

        $this->updateLessonNumbers($courseId);

        return true;
    }

    public function isLessonCountEnough($courseId)
    {
        $lessonCount = $this->countLessons(array('courseId' => $courseId));

        if ($lessonCount >= self::LESSON_LIMIT_NUMBER) {
            $this->createNewException(LessonException::LESSON_NUM_LIMIT());
        }

        return true;
    }

    public function getLessonLimitNum()
    {
        return self::LESSON_LIMIT_NUMBER;
    }

    public function setOptional($courseId, $lessonId)
    {
        $this->getCourseService()->tryManageCourse($courseId);

        $lesson = $this->getLesson($lessonId);
        if (empty($lesson) || 'lesson' != $lesson['type'] || $lesson['courseId'] != $courseId) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $this->beginTransaction();
        try {
            $lesson = $this->getCourseChapterDao()->update($lesson['id'], array('isOptional' => 1));

            $this->getTaskService()->updateTasksOptionalByLessonId($lesson['id'], 1);

            $this->dispatchEvent('course.lesson.setOptional', new Event($lesson));
            $this->getLogService()->info('course', 'lesson_set_optional', "课时设置选修《{$lesson['title']}》", $lesson);

            $this->updateLessonNumbers($courseId);

            $this->commit();

            return $lesson;
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }
    }

    public function unsetOptional($courseId, $lessonId)
    {
        $this->getCourseService()->tryManageCourse($courseId);

        $lesson = $this->getLesson($lessonId);
        if (empty($lesson) || 'lesson' != $lesson['type'] || $lesson['courseId'] != $courseId) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $this->beginTransaction();
        try {
            $lesson = $this->getCourseChapterDao()->update($lesson['id'], array('isOptional' => 0));

            $this->getTaskService()->updateTasksOptionalByLessonId($lesson['id'], 0);

            $this->dispatchEvent('course.lesson.setOptional', new Event($lesson));

            $infoData = array(
                'courseId' => $lesson['courseId'],
                'title' => $lesson['title'],
            );
            $this->getLogService()->info('course', 'lesson_unset_optional', "课时设置必修《{$lesson['title']}》", $infoData);
            $this->updateLessonNumbers($courseId);

            $this->commit();

            return $lesson;
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }
    }

    public function updateLessonNumbers($courseId)
    {
        $lessons = $this->getCourseChapterDao()->search(
            array('courseId' => $courseId, 'type' => 'lesson'),
            array(),
            0,
            10000
        );

        $publishedNum = 1;
        $number = 1;

        $sortedLessons = ArrayToolkit::sortPerArrayValue($lessons, 'seq');

        $batchHelper = new BatchUpdateHelper($this->getCourseChapterDao());

        foreach ($sortedLessons as $lesson) {
            if (!$lesson['isOptional']) {
                $displayedNum = $number;
                ++$number;
            } else {
                $displayedNum = 0;
            }

            if ('published' == $lesson['status'] && !$lesson['isOptional']) {
                $batchHelper->add('id', $lesson['id'], array(
                    'published_number' => $publishedNum,
                    'number' => $displayedNum,
                ));
                ++$publishedNum;
            } else {
                $batchHelper->add('id', $lesson['id'], array(
                    'published_number' => 0,
                    'number' => $displayedNum,
                ));
            }
        }

        $batchHelper->flush();

        foreach ($sortedLessons as $lesson) {
            $this->dispatchEvent('course.lesson.update', new Event($lesson));
        }
    }

    protected function publishTasks($lessonId)
    {
        $tasks = $this->getTaskService()->findTasksByChapterId($lessonId);

        if (empty($tasks)) {
            return;
        }

        foreach ($tasks as $task) {
            $this->getTaskService()->publishTask($task['id']);
        }
    }

    public function findLessonsByCourseId($courseId)
    {
        return $this->getCourseChapterDao()->findLessonsByCourseId($courseId);
    }

    protected function unpublishTasks($lessonId)
    {
        $tasks = $this->getTaskService()->findTasksByChapterId($lessonId);

        if (empty($tasks)) {
            return;
        }

        foreach ($tasks as $task) {
            $this->getTaskService()->unpublishTask($task['id']);
        }
    }

    protected function parseTaskFields($fields)
    {
        if (!empty($fields['startTime'])) {
            $fields['startTime'] = strtotime($fields['startTime']);
        }
        if (!empty($fields['endTime'])) {
            $fields['endTime'] = strtotime($fields['endTime']);
        }

        return $fields;
    }

    protected function getCourseChapterDao()
    {
        return $this->createDao('Course:CourseChapterDao');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
