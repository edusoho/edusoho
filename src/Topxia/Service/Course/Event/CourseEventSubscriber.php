<?php
namespace Topxia\Service\Course\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class CourseEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'course.join' => 'onCourseJoin',
            'course.favorite' => 'onCourseFavorite',
            'course.note.create' => 'onCourseNoteCreate',
            'course.note.update' => 'onCourseNoteUpdate',
            'course.note.delete' => 'onCourseNoteDelete',
            'course.note.liked' => 'onCourseNoteLike',
            'course.note.cancelLike' => 'onCourseNoteCancelLike',
            'course.update' => 'onCourseUpdate',
            'course.teacher.update' => array('onCourseTeacherUpdate', 0),
            'material.create' => 'onMaterialCreate',
            'chapter.create' => 'onChapterCreate',
            'chapter.delete' => 'onChapterDelete',
            'chapter.update' => 'onChapterUpdate',
            'course.member.create' => 'onCourseMemberCreate',
            'course.member.delete' => 'onCourseMemberDelete'
        );
    }

    public function onCourseJoin(ServiceEvent $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');
        $this->getStatusService()->publishStatus(array(
            'type' => 'become_student',
            'courseId' => $course['id'],
            'objectType' => 'course',
            'objectId' => $course['id'],
            'private' => $course['status'] == 'published' ? 0 : 1,
            'userId' => $userId,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
            ),
        ));
    }

    public function onCourseFavorite(ServiceEvent $event)
    {
        $course = $event->getSubject();
        $this->getStatusService()->publishStatus(array(
            'type' => 'favorite_course',
            'courseId' => $course['id'],
            'objectType' => 'course',
            'objectId' => $course['id'],
            'private' => $course['status'] == 'published' ? 0 : 1,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
            ),
        ));
    }

    public function onCourseNoteCreate(ServiceEvent $event)
    {
        $note = $event->getSubject();
        $classroom = $this->getClassroomService()->findClassroomByCourseId($note['courseId']);
        $course = $this->getCourseService()->getCourse($note['courseId']);
        if ($classroom && $note['status']) {
            $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', +1);
        }

        if ($course && $note['status']) {
            $this->getCourseService()->waveCourse($note['courseId'], 'noteNum', +1);
        }
    }

    public function onCourseNoteUpdate(ServiceEvent $event)
    {
        $note = $event->getSubject();
        $preStatus = $event->getArgument('preStatus');
        $classroom = $this->getClassroomService()->findClassroomByCourseId($note['courseId']);
        $course = $this->getCourseService()->getCourse($note['courseId']);
        if ($classroom && $note['status'] && !$preStatus) {
            $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', +1);
        }

        if ($classroom && !$note['status'] && $preStatus) {
            $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', -1);
        }

        if ($course && $note['status'] && !$preStatus) {
            $this->getCourseService()->waveCourse($note['courseId'], 'noteNum', +1);
        }

        if ($course && !$note['status'] && $preStatus) {
            $this->getCourseService()->waveCourse($note['courseId'], 'noteNum', -1);
        }

    }

    public function onCourseNoteDelete(ServiceEvent $event)
    {
        $note = $event->getSubject();
        $classroom = $this->getClassroomService()->findClassroomByCourseId($note['courseId']);
        if ($classroom) {
            $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', -1);
        }

        $course = $this->getCourseService()->getCourse($note['courseId']);
        if ($course) {
            $this->getCourseService()->waveCourse($note['courseId'], 'noteNum', -1);
        }
    }

    public function onCourseNoteLike(ServiceEvent $event)
    {
        $note = $event->getSubject();
        $this->getNoteService()->count($note['id'], 'likeNum', +1);
    }

    public function onCourseNoteCancelLike(ServiceEvent $event)
    {
        $note = $event->getSubject();
        $this->getNoteService()->count($note['id'], 'likeNum', -1);
    }

    public function onCourseUpdate(ServiceEvent $event)
    {   
        $course = $event->getSubject();
        $parentId = $course['id'];
        unset($course['id'],$course['parentId'],$course['hitNum'],$course['locked']);
        $this->getCourseService()->updateCourseByParentIdAndLocked($parentId,1,$course);
    }

    public function onCourseTeacherUpdate(ServiceEvent $event)
    {
        $context = $event->getSubject();

        $courseId = $context["courseId"];

        $findClassroomsByCourseIds =  $this->getClassroomService()->findClassroomIdsByCourseId($courseId);

        foreach ($findClassroomsByCourseIds as $findClassroomsByCourseId) {
            $this->getClassroomService()->updateClassroomTeachers($findClassroomsByCourseId);
        }


        $course = $context['course'];

        $this->getCourseService()->updateCourseByParentIdAndLocked($courseId, 1, array('teacherIds'=>$course['teacherIds']));

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');
        
        foreach ($courseIds as $courseId) {
            $findClassroomsByCourseIds =  $this->getClassroomService()->findClassroomIdsByCourseId($courseId);
            foreach ($findClassroomsByCourseIds as $findClassroomsByCourseId) {
                $this->getClassroomService()->updateClassroomTeachers($findClassroomsByCourseId);
            }
        }
    }

    public function onMaterialCreate(ServiceEvent $event)
    {
        $material = $event->getSubject();
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($material['courseId'],1),'id');
        $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByParentId($material['lessonId']),'id');
        $material['pId'] = $material['id'];
        $lesson = $this->getCourseService()->getLesson($material['lessonId']);
        $parentId = $lesson['id'];
        unset($material['id']);
        unset($lesson['id'],$lesson['courseId'],$lesson['chapterId'],$lesson['parentId']);
        foreach ($courseIds as $key => $value) {
           $material['courseId'] = $value;
           $material['lessonId'] = $lessonIds[$key];
           $this->getMaterialService()->createMaterial($material);
        }
        $this->getCourseService()->updateLessonByParentId($parentId,$lesson);

    }

    public function onChapterCreate(ServiceEvent $event)
    {
        $chapter = $event->getSubject();
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($chapter['courseId'],1),'id');
        $chapter['pId'] = $chapter['id'];
        unset($chapter['id']);
        foreach ($courseIds as  $value) {
            $chapter['courseId'] = $value;
            $this->getCourseService()->addChapter($chapter);
        }
    }

    public function onChapterDelete(ServiceEvent $event)
    {
        $chapter = $event->getSubject();
        $this->getCourseService()->deleteChapterByPId($chapter['id']);
    }

    public function onChapterUpdate(ServiceEvent $event)
    {
        $chapter = $event->getSubject();
        $pId = $chapter['id'];
        unset($chapter['id'],$chapter['courseId'],$chapter['pId']);
        $this->getCourseService()->updateChapterByPId($pId,$chapter);
    }

    public function onCourseMemberCreate(ServiceEvent $event)
    {
       $member = $event->getSubject();
       $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($member['courseId'],1),'id');
       unset($member['id']);
       foreach ($courseIds as $courseId) {
           $member['courseId'] = $courseId;
           $this->getCourseService()->createMember($member);
       }
    }

    public function onCourseMemberDelete(ServiceEvent $event)
    {
       $member = $event->getSubject();
       $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($member['courseId'],1),'id');
       foreach ($courseIds as $courseId) {
           $this->getCourseService()->deleteMemberByCourseIdAndUserId($courseId,$member['userId']);
       } 
    }

    protected function simplifyCousrse($course)
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

    protected function simplifyLesson($lesson)
    {
        return array(
            'id' => $lesson['id'],
            'number' => $lesson['number'],
            'type' => $lesson['type'],
            'title' => $lesson['title'],
            'summary' => StringToolkit::plain($lesson['summary'], 100),
        );
    }

    protected function getStatusService()
    {
        return ServiceKernel::instance()->createService('User.StatusService');
    }

    protected function getNoteService()
    {
        return ServiceKernel::instance()->createService('Course.NoteService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getMaterialService()
    {
        return ServiceKernel::instance()->createService('Course.MaterialService');
    }

}
