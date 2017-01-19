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
        );
    }

    public function onCourseMaterialCreate(Event $event)
    {
        $material = $event->getSubject();

        $this->getCourseService()->updateCourseStatistics($material['courseId'], array('materialNum'));
        $this->getCourseSetService()->updateCourseSetStatistics($material['courseSetId'], array('materialNum'));
    }

    public function onCourseMaterialUpdate(Event $event)
    {
        $material = $event->getSubject();

        $this->getCourseService()->updateCourseStatistics($material['courseId'], array('materialNum'));
        $this->getCourseSetService()->updateCourseSetStatistics($material['courseSetId'], array('materialNum'));
    }

    public function onCourseMaterialDelete(Event $event)
    {
        $material = $event->getSubject();
        $this->getCourseService()->updateCourseStatistics($material['courseId'], array('materialNum'));
        $this->getCourseSetService()->updateCourseSetStatistics($material['courseSetId'], array('materialNum'));
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