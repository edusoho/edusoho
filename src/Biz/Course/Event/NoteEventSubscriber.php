<?php


namespace Biz\Course\Event;


use Biz\Course\Service\CourseService;
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
        $course = $this->getCourseService()->getCourse($note['courseId']);

        // @TODO 班级功能改造完后完善
        /*if ($classroom && $note['status']) {
            $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', +1);
        }*/

        if ($course && $note['status']) {
            $this->getCourseService()->waveNoteNum($note['courseId'], +1);
        }
    }

    public function onCourseNoteUpdate(Event $event)
    {
        $note      = $event->getSubject();
        $preStatus = $event->getArgument('preStatus');
        $course    = $this->getCourseService()->getCourse($note['courseId']);

        if (empty($course)) {
            return;
        }

        // @TODO 班级功能改造完后完善
        //$classroom = $this->getClassroomService()->getClassroomByCourseId($note['courseId']);
        /*if ($classroom && $note['status'] && !$preStatus) {
            $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', +1);
        }

        if ($classroom && !$note['status'] && $preStatus) {
            $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', -1);
        }*/

        if ($note['status'] == CourseNoteService::PUBLIC_STATUS
            && $preStatus == CourseNoteService::PRIVATE_STATUS
        ) {
            $this->getCourseService()->waveNoteNum($note['courseId'], +1);
        } else if ($note['status'] == CourseNoteService::PRIVATE_STATUS
            && $preStatus == CourseNoteService::PUBLIC_STATUS
        ) {
            $this->getCourseService()->waveNoteNum($note['courseId'], -1);
        }
    }

    public function onCourseNoteDelete(Event $event)
    {
        $note      = $event->getSubject();
        $needWave  = $note['status'] == CourseNoteService::PUBLIC_STATUS;

        /*$classroom = $this->getClassroomService()->getClassroomByCourseId($note['courseId']);
        if ($classroom) {
            $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', -1);
        }*/

        $course = $this->getCourseService()->getCourse($note['courseId']);
        if (!empty($course) && $needWave) {
            $this->getCourseService()->waveNoteNum($note['courseId'], -1);
        }
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
}