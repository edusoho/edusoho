<?php
namespace Topxia\Service\Notification;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class PushMessageEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'testpaper.reviewed' => 'onTestPaperReviewed',
            'course.lesson.create' => 'onLessonCreate',
            'course.publish' => 'onCoursePublish',
            'course.close' => 'onCourseClose',
            'announcement.create' => 'onAnnouncementCreate',
            'classroom.join' => 'onClassroomJoin',
            'classroom.put_course' => 'onClassroomPutCourse',
            'article.create' => 'onArticleCreate',
            'discount.start' => 'onDiscountStart',
        );
    }

    public function onTestPaperReviewed(ServiceEvent $event)
    {
        $result = $event->getSubject();

        $from = array(
            'type' => 'course',
            'id' => 1,
            'image' => '',
        );

        $to = array('type' => 'user', 'id' => $result['userId']);

        $body = array(
            'type' => 'testpaper.reviewed',
            'resultId' => $result['id'],
            'testpaperId' => $result['testId'],
            'lessonId' => 1,
            'score' => $result['score'],
            'teacherSay' => $result['teacherSay'],
        );

        file_put_contents('/tmp/push_message', json_encode($subject));
    }

    public function onCoursePublish(ServiceEvent $event)
    {

    }

    public function onCourseClose(ServiceEvent $event)
    {

    }

    public function onAnnouncementCreate(ServiceEvent $event)
    {

    }

    public function onClassroomJoin(ServiceEvent $event)
    {

    }

    public function onClassroomPutCourse(ServiceEvent $event)
    {

    }

    public function onArticleCreate(ServiceEvent $event)
    {

    }

    public function onDiscountStart(ServiceEvent $event)
    {
        $subject = $event->getSubject();
        file_put_contents('/tmp/push_message', json_encode($subject));
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

}
