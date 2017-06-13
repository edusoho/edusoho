<?php

namespace Biz\Course\Event;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MaterialEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.material.create' => 'onCourseMaterialCreate',
            'course.material.update' => 'onCourseMaterialUpdate',
            'course.material.delete' => 'onCourseMaterialDelete',
            'course.lesson.materials.delete' => 'onCourseLessonMaterialsDelete',
        );
    }

    public function onCourseMaterialCreate(Event $event)
    {
        $this->updateMaterialNum($event);
    }

    public function onCourseMaterialUpdate(Event $event)
    {
        $this->updateMaterialNum($event);
    }

    public function onCourseMaterialDelete(Event $event)
    {
        $this->updateMaterialNum($event);
    }

    protected function updateMaterialNum($event)
    {
        $material = $event->getSubject();
        $this->getCourseService()->updateCourseStatistics($material['courseId'], array('materialNum'));
        if (!empty($material['courseSetId'])) {
            $this->getCourseSetService()->updateCourseSetStatistics($material['courseSetId'], array('materialNum'));
        }
    }

    public function onCourseLessonMaterialsDelete(Event $event)
    {
        $lesson = $event->getSubject();
        $activity = $this->getActivityService()->getActivity($lesson['lessonId']);
        $this->getCourseService()->updateCourseStatistics($activity['fromCourseId'], array('materialNum'));
        if (!empty($material['courseSetId'])) {
            $this->getCourseSetService()->updateCourseSetStatistics($activity['fromCourseSetId'], array('materialNum'));
        }
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

    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }
}
