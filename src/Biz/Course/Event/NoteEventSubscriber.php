<?php

namespace Biz\Course\Event;

use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseNoteService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NoteEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'course.note.create' => 'onCourseNoteCreate',
            'course.note.update' => 'onCourseNoteUpdate',
            'course.note.delete' => 'onCourseNoteDelete',
            'course.note.liked' => 'onCourseNoteLike',
            'course.note.cancelLike' => 'onCourseNoteCancelLike',
        ];
    }

    public function onCourseNoteCreate(Event $event)
    {
        $note = $event->getSubject();

        $classroom = $this->getClassroomService()->getClassroomByCourseId($note['courseId']);

        if ($classroom && CourseNoteService::PUBLIC_STATUS == $note['status']) {
            $this->getClassroomService()->waveClassroom($classroom['id'], 'noteNum', +1);
        }

        if ($classroom) {
            $this->getClassroomService()->updateMemberFieldsByClassroomIdAndUserId($classroom['id'], $note['userId'], ['noteNum']);
        }

        $this->getCourseMemberService()->refreshMemberNoteNumber($note['courseId'], $note['userId']);
        $this->getCourseService()->updateCourseStatistics($note['courseId'], ['noteNum']);
        $this->getCourseSetService()->updateCourseSetStatistics($note['courseSetId'], ['noteNum']);
    }

    public function onCourseNoteUpdate(Event $event)
    {
        $note = $event->getSubject();
        $this->getCourseService()->updateCourseStatistics($note['courseId'], ['noteNum']);
        $this->getCourseSetService()->updateCourseSetStatistics($note['courseSetId'], ['noteNum']);
        $this->getCourseMemberService()->refreshMemberNoteNumber($note['courseId'], $note['userId']);

        $classroom = $this->getClassroomService()->getClassroomByCourseId($note['courseId']);

        if (empty($classroom)) {
            return;
        }

        $preStatus = $event->getArgument('preStatus');

        if (CourseNoteService::PUBLIC_STATUS == $note['status'] && CourseNoteService::PRIVATE_STATUS == $preStatus) {
            $this->getClassroomService()->waveClassroom($classroom['id'], 'noteNum', +1);
        }

        if (CourseNoteService::PRIVATE_STATUS == $note['status'] && CourseNoteService::PUBLIC_STATUS == $preStatus) {
            $this->getClassroomService()->waveClassroom($classroom['id'], 'noteNum', -1);
        }
    }

    public function onCourseNoteDelete(Event $event)
    {
        $note = $event->getSubject();

        $classroom = $this->getClassroomService()->getClassroomByCourseId($note['courseId']);

        if (!empty($classroom)) {
            $this->getClassroomService()->waveClassroom($classroom['id'], 'noteNum', -1);
            $this->getClassroomService()->updateMemberFieldsByClassroomIdAndUserId($classroom['id'], $note['userId'], ['noteNum']);
        }

        $this->getCourseService()->updateCourseStatistics($note['courseId'], ['noteNum']);
        $this->getCourseSetService()->updateCourseSetStatistics($note['courseSetId'], ['noteNum']);
        $this->getCourseMemberService()->refreshMemberNoteNumber($note['courseId'], $note['userId']);
    }

    public function onCourseNoteLike(Event $event)
    {
        $note = $event->getSubject();
        $this->getCourseNoteService()->waveLikeNum($note['id'], +1);
    }

    public function onCourseNoteCancelLike(Event $event)
    {
        $note = $event->getSubject();
        $this->getCourseNoteService()->waveLikeNum($note['id'], -1);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return CourseNoteService
     */
    protected function getCourseNoteService()
    {
        return $this->getBiz()->service('Course:CourseNoteService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }
}
