<?php
namespace Topxia\Service\User\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class StatusEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'status.lesson_start' => 'onLessonStart',
            'status.lesson_finish' => 'onLessonFinish',
            'status.testpaper_finish' => 'onTestpaperFinish',
            'status.homework_finish' => 'onHomeworkFinish',
            'status.exercise_finish' => 'onExerciseFinish',
            'status.course_join' => 'onCourseJoin',
            'status.course_favorite' => 'onCourseFavorite',
            'status.classroom_join' => 'onClassroomJoin',
        );
    }

    public function onLessonStart(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $course = $event->getArgument('course');
        $this->getStatusService()->publishStatus(array(
            'type' => 'start_learn_lesson',
            'objectType' => 'lesson',
            'objectId' => $lesson['id'],
            'isHidden' => $course['status'] == 'published' ? 0 : 1,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
                'lesson' => $this->simplifyLesson($lesson),
            )
        ));
    }

    public function onLessonFinish(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $course = $event->getArgument('course');
        $this->getStatusService()->publishStatus(array(
            'type' => 'learned_lesson',
            'objectType' => 'lesson',
            'objectId' => $lesson['id'],
            'isHidden' => $course['status'] == 'published' ? 0 : 1,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
                'lesson' => $this->simplifyLesson($lesson),
            )
        ));
    }

    public function onThreadCreate(ServiceEvent $event)
    {
        $this->callTargetEventProcessor('onThreadCreate', $event);
    }

    public function onThreadNice(ServiceEvent $event)
    {
        $this->callTargetEventProcessor('onThreadNice', $event);
    }

    public function onThreadSticky(ServiceEvent $event)
    {
        $this->callTargetEventProcessor('onThreadSticky', $event);
    }

    public function onPostCreate(ServiceEvent $event)
    {
        $this->callTargetEventProcessor('onPostCreate', $event);
    }

    public function onPostDelete(ServiceEvent $event)
    {
        $this->callTargetEventProcessor('onPostDelete', $event);
    }

    public function onPostVote(ServiceEvent $event)
    {
        $this->callTargetEventProcessor('onPostVote', $event);
    }

    private function simplifyCousrse($course)
    {
        return array(
            'id' => $course['id'],
            'title' => $course['title'],
            'picture' => $course['middlePicture'],
            'type' => $course['type'],
            'rating' => $course['rating'],
            'about' => StringToolkit::plain($course['about'], 100),
            'price' => $course['price'],
        );
    }

    private function simplifyLesson($lesson)
    {
        return array(
            'id' => $lesson['id'],
            'number' => $lesson['number'],
            'type' => $lesson['type'],
            'title' => $lesson['title'],
            'summary' => StringToolkit::plain($lesson['summary'], 100),
        );
    }

    private function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    private function getStatusService()
    {
        return ServiceKernel::instance()->createService('User.StatusService');
    }
}
