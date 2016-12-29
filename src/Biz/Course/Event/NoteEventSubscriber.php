<?php


namespace Biz\Course\Event;


use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Note\Service\CourseNoteService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NoteEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.note.create'     => 'onCourseNoteCreate',
            'course.note.update'     => 'onCourseNoteUpdate',
            'course.note.delete'     => 'onCourseNoteDelete',
            'course.note.liked'      => 'onCourseNoteLike',
            'course.note.cancelLike' => 'onCourseNoteCancelLike',
        );
    }

    public function onCourseNoteCreate(Event $event)
    {
        $note = $event->getSubject();
        //$classroom = $this->getClassroomService()->getClassroomByCourseId($note['courseId']);


        // @TODO 班级功能改造完后完善
        /*if ($classroom && $note['status']) {
            $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', +1);
        }*/

        $this->getCourseMemberService()->refreshMemberNoteNumber($note['courseId'], $note['userId']);
        $this->getCourseService()->updateCourseStatistics($note['courseId'], array('noteNum'));
    }

    public function onCourseNoteUpdate(Event $event)
    {
        $note      = $event->getSubject();
        $this->getCourseService()->updateCourseStatistics($note['courseId'], array('noteNum'));
        $this->getCourseMemberService()->refreshMemberNoteNumber($note['courseId'], $note['userId']);

        // @TODO 班级功能改造完后完善
        //$classroom = $this->getClassroomService()->getClassroomByCourseId($note['courseId']);
        /*if ($classroom && $note['status'] && !$preStatus) {
            $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', +1);
        }

        if ($classroom && !$note['status'] && $preStatus) {
            $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', -1);
        }*/

    }

    public function onCourseNoteDelete(Event $event)
    {
        $note      = $event->getSubject();
        /*$classroom = $this->getClassroomService()->getClassroomByCourseId($note['courseId']);
        if ($classroom) {
            $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', -1);
        }*/

        $this->getCourseService()->updateCourseStatistics($note['courseId'], array('noteNum'));
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
        return $this->getBiz()->service('Note:CourseNoteService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }
}