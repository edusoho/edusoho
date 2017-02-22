<?php
namespace Classroom\Service\Classroom\Event;

use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Taxonomy\TagOwnerManager;
use Topxia\Common\ArrayToolkit;

class ClassroomEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'classroom.delete'       => 'onClassroomDelete',
            'classroom.update'       => 'onClassroomUpdate',
            'classroom.join'         => 'onClassroomJoin',
            'classroom.auditor_join' => 'onClassroomGuest',
            'classReview.add'        => 'onReviewCreate'
        );
    }

    public function onClassroomDelete(ServiceEvent $event)
    {
        $classroom = $event->getSubject();

        $tagOwnerManager = new TagOwnerManager('classroom', $classroom['id']);
        $tagOwnerManager->delete();
    }

    public function onClassroomUpdate(ServiceEvent $event)
    {
        $classroom = $event->getSubject();

        $userId      = $classroom['userId'];
        $classroomId = $classroom['classroomId'];
        $expiryDate  = array(
            'expiryMode' => empty($classroom['fields']['expiryMode']) ? null : $classroom['fields']['expiryMode'],
            'expiryDay'  => empty($classroom['fields']['expiryDay']) ? null : $classroom['fields']['expiryDay']
        );

        if (isset($classroom['tagIds'])) {
            $tagOwnerManager = new TagOwnerManager('classroom', $classroomId, $classroom['tagIds'], $userId);
            $tagOwnerManager->update();
        }

        if (!empty($expiryDate['expiryMode']) && !empty($expiryDate['expiryDay'])) {
            $classroom = $this->getClassroomService()->getClassroom($classroomId);

            if ($classroom['expiryMode'] == 'date' || $classroom['status'] != 'published') {
                $this->updateClassroomMembers($classroomId, $expiryDate);
            }

            $this->updateClassroomCopyCourses($classroomId, $expiryDate);
        }
    }

    protected function updateClassroomCopyCourses($classroomId, $expiryDate)
    {
        $activeCourses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);

        foreach ($activeCourses as $course) {
            $this->getCourseDao()->updateCourse(
                $course['id'], 
                array(
                    'expiryMode' => $expiryDate['expiryMode'], 
                    'expiryDay'  => $expiryDate['expiryDay']
                )
            );
        }
    }

    protected function updateClassroomMembers($classroomId, $expiryDate)
    {
        $members = $this->getClassroomService()->findMembersByClassroomId($classroomId);

        if ($expiryDate['expiryMode'] == 'days') {
            $classroom = $this->getClassroomService()->getClassroom($classroomId);

            $expiryDate['expiryDay'] = $classroom['createdTime'] + $expiryDate['expiryDay'] * 24 * 60 * 60;
        }

        foreach ($members as $member) {
            $this->getClassroomService()->updateMember(
                $member['id'], 
                array(
                    'deadline' => $expiryDate['expiryDay']
                )
            );
        }
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

    private function getCourseDao()
    {
        return ServiceKernel::instance()->createDao('Course.CourseDao');
    }

    protected function getNotifiactionService()
    {
        return ServiceKernel::instance()->createService('User.NotificationService');
    }

    private function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    private function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    private function getClassroomReviewService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomReviewService');
    }
}
