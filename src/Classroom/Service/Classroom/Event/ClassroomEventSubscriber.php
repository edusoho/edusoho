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
        $fields = $event->getSubject();

        $userId      = $fields['userId'];
        $classroomId = $fields['classroomId'];
        $expiryDate  = array(
            'expiryMode' => empty($fields['fields']['expiryMode']) ? null : $fields['fields']['expiryMode'],
            'expiryDay'  => empty($fields['fields']['expiryDay']) ? null : $fields['fields']['expiryDay']
        );

        if (isset($fields['tagIds'])) {
            $tagOwnerManager = new TagOwnerManager('classroom', $classroomId, $fields['tagIds'], $userId);
            $tagOwnerManager->update();
        }

        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        if (!empty($expiryDate['expiryMode']) && !empty($expiryDate['expiryDay'])) {
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
            $this->getCourseDao()->updateCourse($course['id'], array('expiryMode' => $expiryDate['expiryMode'], 'expiryDay' => $expiryDate['expiryDay']));
        }
    }

    protected function updateClassroomMembers($classroomId, $expiryDate)
    {
        $studentsIds = $this->getClassroomService()->findMemberUserIdsByClassroomId($classroomId);

        if ($expiryDate['expiryMode'] == 'days') {
            $classroom = $this->getClassroomService()->getClassroom($classroomId);

            $expiryDate['expiryDay'] = $classroom['createdTime'] + $expiryDate['expiryDay'] * 24 * 60 * 60;
        }

        foreach ($studentsIds as $studentId) {
            $member = $this->getClassroomService()->getClassroomMember($classroomId, $studentId);

            if ($member['role'][0] == 'student') {
                $this->getClassroomService()->updateMember($member['id'], array('deadline' => $expiryDate['expiryDay']));
            }
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
