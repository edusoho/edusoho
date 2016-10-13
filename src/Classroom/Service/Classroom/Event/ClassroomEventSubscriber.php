<?php
namespace Classroom\Service\Classroom\Event;

use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClassroomEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'classroom.join'         => 'onClassroomJoin',
            'classroom.auditor_join' => 'onClassroomGuest',
            'classReview.add'        => 'onReviewCreate'
        );
    }

    public function onClassroomJoin(ServiceEvent $event)
    {
        $classroom = $event->getSubject();
        $userId    = $event->getArgument('userId');
        $this->getStatusService()->publishStatus(array(
            'type'        => 'become_student',
            'classroomId' => $classroom['id'],
            'objectType'  => 'classroom',
            'objectId'    => $classroom['id'],
            'private'     => $classroom['status'] == 'published' ? 0 : 1,
            'private'     => $classroom['showable'] == 1 ? 0 : 1,
            'userId'      => $userId,
            'properties'  => array(
                'classroom' => $this->simplifyClassroom($classroom)
            )
        ));
    }

    public function onClassroomGuest(ServiceEvent $event)
    {
        $classroom = $event->getSubject();
        $userId    = $event->getArgument('userId');
        $this->getStatusService()->publishStatus(array(
            'type'        => 'become_auditor',
            'classroomId' => $classroom['id'],
            'objectType'  => 'classroom',
            'objectId'    => $classroom['id'],
            'private'     => $classroom['status'] == 'published' ? 0 : 1,
            'private'     => $classroom['showable'] == 1 ? 0 : 1,
            'userId'      => $userId,
            'properties'  => array(
                'classroom' => $this->simplifyClassroom($classroom)
            )
        ));
    }

    public function onReviewCreate(ServiceEvent $event)
    {
        $review = $event->getSubject();

        if ($review['parentId'] > 0) {
            $classroom = $this->getClassroomService()->getClassroom($review['classroomId']);

            $parentReview = $this->getClassroomReviewService()->getReview($review['parentId']);
            if (!$parentReview) {
                return false;
            }

            $message = array(
                'title'      => $classroom['title'],
                'targetId'   => $review['classroomId'],
                'targetType' => 'classroom',
                'userId'     => $review['userId']
            );
            $this->getNotifiactionService()->notify($parentReview['userId'], 'comment-post',
                $message);
        }
    }

    private function simplifyClassroom($classroom)
    {
        return array(
            'id'      => $classroom['id'],
            'title'   => $classroom['title'],
            'picture' => $classroom['middlePicture'],
            'about'   => StringToolkit::plain($classroom['about'], 100),
            'price'   => $classroom['price']
        );
    }

    private function getStatusService()
    {
        return ServiceKernel::instance()->createService('User.StatusService');
    }

    protected function getNotifiactionService()
    {
        return ServiceKernel::instance()->createService('User.NotificationService');
    }

    private function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    private function getClassroomReviewService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomReviewService');
    }
}
