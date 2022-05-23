<?php

namespace MarketingMallBundle\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Codeages\Biz\Framework\Event\Event;
use MarketingMallBundle\Common\GoodsContentBuilder\CourseInfoBuilder;

class CourseEventSubscriber extends BaseEventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'course-set.update' => 'onCourseSetUpdate',
            'course.teachers.update' => 'onCourseTeacherUpdate',
            'course.marketing.update' => 'onCourseMarketingUpdate',
            'course.lesson.update_status' => 'onCourseLessonUpdateStatus',
            'course.lesson.batch_update_status' => 'onCourseLessonBatchUpdateStatus',
            'course.lesson.batch_delete' => 'onCourseLessonBatchDelete',
            'course.items.sort' => 'onCourseItemsSort',
            'course.chapter.update' => 'onCourseChapterUpdate',
        ];
    }

    public function onCourseSetUpdate(Event $event)
    {
        $courseSet = $event->getSubject();
        if (!$event->hasArgument('oldCourseSet')) {
            return;
        }
        $oldCourseSet = $event->getArgument('oldCourseSet');
        $syncFields = ['summary', 'cover'];
        foreach ($syncFields as $syncField) {
            if ($courseSet[$syncField] !== $oldCourseSet[$syncField]) {
                $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);
                foreach ($courses as $course) {
                    $this->syncCourseToMarketingMall($course['id']);
                }
                return;
            }
        }
        $syncFields = ['title', 'subtitle'];
        foreach ($syncFields as $syncField) {
            if ($courseSet[$syncField] !== $oldCourseSet[$syncField]) {
                $this->syncCourseToMarketingMall($courseSet['defaultCourseId']);
                return;
            }
        }
    }

    public function onCourseTeacherUpdate(Event $event)
    {
        $course = $event->getSubject();
        $teachers = $event->getArgument('teachers');
        $teachers = ArrayToolkit::group($teachers, 'isVisible');
        $visibleTeacherIds = empty($teachers[1]) ? [] : array_column($teachers[1], 'id');
        if ($course['teacherIds'] != $visibleTeacherIds) {
            $this->syncCourseToMarketingMall($course['id']);
        }
    }

    public function onCourseMarketingUpdate(Event $event)
    {
        $subject = $event->getSubject();
        $oldCourse = $subject['oldCourse'];
        $course = $subject['newCourse'];
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        if ($courseSet['defaultCourseId'] == $course['id']) {
            return;
        }
        $syncFields = ['title', 'subtitle'];
        foreach ($syncFields as $syncField) {
            if ($course[$syncField] !== $oldCourse[$syncField]) {
                $this->syncCourseToMarketingMall($course['id']);
                return;
            }
        }
    }

    public function onCourseLessonUpdateStatus(Event $event)
    {
        $lesson = $event->getSubject();
        $this->syncCourseToMarketingMall($lesson['courseId']);
    }

    public function onCourseLessonBatchUpdateStatus(Event $event)
    {
        $courseId = $event->getArgument('courseId');
        $this->syncCourseToMarketingMall($courseId);
    }

    public function onCourseLessonBatchDelete(Event $event)
    {
        $courseId = $event->getArgument('courseId');
        $this->syncCourseToMarketingMall($courseId);
    }

    public function onCourseItemsSort(Event $event)
    {
        $courseId = $event->getArgument('courseId');
        $this->syncCourseToMarketingMall($courseId);
    }

    public function onCourseChapterUpdate(Event $event)
    {
        $chapter = $event->getSubject();
        $oldChapter = $event->getArgument('oldChapter');
        if ($chapter['title'] != $oldChapter['title']) {
            $this->syncCourseToMarketingMall($chapter['courseId']);
        }
    }

    protected function syncCourseToMarketingMall($courseId)
    {
        // TODO: 判断是否是商城中的商品.
        $this->updateGoodsContent('course', new CourseInfoBuilder(), $courseId);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }
}
