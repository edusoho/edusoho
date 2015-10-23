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
            'material.delete' => 'onMaterialDelete',
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
        $private = $course['status'] == 'published' ? 0 :1;
        if($course['parentId']){ 
            $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']); 
            $classroom = $this->getClassroomService()->getClassroom($classroom['classroomId']);
            if(array_key_exists('showable',$classroom) &&$classroom['showable']==1) {
                $private = 0;
            }else{
                $private = 1;
            }
        }
        $userId = $event->getArgument('userId');
        $this->getStatusService()->publishStatus(array(
            'type' => 'become_student',
            'courseId' => $course['id'],
            'objectType' => 'course',
            'objectId' => $course['id'],
            'private' => $private,
            'userId' => $userId,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
            ),
        ));
    }

    public function onCourseFavorite(ServiceEvent $event)
    {
        $course = $event->getSubject();
        $private = $course['status'] == 'published' ? 0 :1;
        if($course['parentId']){ 
            $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']); 
            $classroom = $this->getClassroomService()->getClassroom($classroom['classroomId']);
            if(array_key_exists('showable',$classroom) && $classroom['showable']==1) {
                $private = 0;
            }else{
                $private = 1;
            }
        }
        $this->getStatusService()->publishStatus(array(
            'type' => 'favorite_course',
            'courseId' => $course['id'],
            'objectType' => 'course',
            'objectId' => $course['id'],
            'private' => $private,
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
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($course['id'],1),'id');
        unset($course['id'],$course['parentId'],$course['hitNum'],$course['locked'],$course['noteNum'],$course['createdTime'],$course['studentNum'],$course['rating'],$course['ratingNum'],$course['income'],$course['showStudentNumType'],$course['useInClassroom'],$course['singleBuy'],$course['daysOfNotifyBeforeDeadline']);
        if ($courseIds) {
            foreach ($courseIds as $courseId) {
                $this->getCourseService()->editCourse($courseId, $course);
            }
        }
    }

    public function onCourseTeacherUpdate(ServiceEvent $event)
    {
        $context = $event->getSubject();

        $courseId = $context["courseId"];

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');

        
        $findClassroomsByCourseIds =  $this->getClassroomService()->findClassroomIdsByCourseId($courseId);

        foreach ($findClassroomsByCourseIds as $findClassroomsByCourseId) {
            $this->getClassroomService()->updateClassroomTeachers($findClassroomsByCourseId);
        }

        if ($courseIds) {
            
            $course = $context['course'];

            foreach ($courseIds as $courseId) {
                $this->getCourseService()->editCourse($courseId, array('teacherIds'=>$course['teacherIds']));
            }
        
            foreach ($courseIds as $courseId) {
                $findClassroomsByCourseIds =  $this->getClassroomService()->findClassroomIdsByCourseId($courseId);
                foreach ($findClassroomsByCourseIds as $findClassroomsByCourseId) {
                    $this->getClassroomService()->updateClassroomTeachers($findClassroomsByCourseId);
                }
            }

        }
    }

    public function onMaterialCreate(ServiceEvent $event)
    {
        $material = $event->getSubject();
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($material['courseId'],1),'id');
        if ($courseIds) {
            $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByParentIdAndLockedCourseIds($material['lessonId'],$courseIds),'id');
            $material['pId'] = $material['id'];
            $lesson = $this->getCourseService()->getLesson($material['lessonId']);
            unset($material['id']);
            unset($lesson['id'],$lesson['courseId'],$lesson['chapterId'],$lesson['parentId']);
            foreach ($courseIds as $key => $courseId) {
                $material['courseId'] = $courseId;
                $material['lessonId'] = $lessonIds[$key];
                if(!empty($material['fileId'])){
                    $this->getUploadFileService()->waveUploadFile($material['fileId'], 'usedCount', 1);
                }
               $this->getMaterialService()->createMaterial($material);
            }
            foreach ($courseIds as $key => $courseId) {
               $this->getCourseService()->editLesson($lessonIds[$key], $lesson);
            } 
        }
    }

    public function onMaterialDelete(ServiceEvent $event)
    {
        $material = $event->getSubject();
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($material['courseId'],1),'id');
        if ($courseIds) {
            $materialIds = ArrayToolkit::column($this->getMaterialService()->findMaterialsByPIdAndLockedCourseIds($material['id'],$courseIds),'id');
            foreach ($materialIds as $key=>$materialId) {
                if(!empty($material['fileId'])){
                    $this->getUploadFileService()->waveUploadFile($material['fileId'],'usedCount',-1);
                }
                $this->getMaterialService()->deleteMaterial($courseIds[$key],$materialId);
            }
        }
    }

    public function onChapterCreate(ServiceEvent $event)
    {
        $chapter = $event->getSubject();
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($chapter['courseId'],1),'id');
        if ($courseIds){
            $chapter['pId'] = $chapter['id'];
            unset($chapter['id']);
            foreach ($courseIds as  $value) {
                $chapter['courseId'] = $value;
                $this->getCourseService()->addChapter($chapter);
            }
        }
    }

    public function onChapterDelete(ServiceEvent $event)
    {
        $chapter = $event->getSubject();
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($chapter['courseId'],1),'id');
        if ($courseIds) {
           $chapterIds = ArrayToolkit::column($this->getCourseService()->findChaptersByChapterIdAndLockedCourseIds($chapter['id'], $courseIds),'id');
           foreach ($chapterIds as $key=>$chapterId) {
               $this->getCourseService()->deleteChapter($courseIds[$key],$chapterId);
           }
        }
        
    }

    public function onChapterUpdate(ServiceEvent $event)
    {
        $chapter = $event->getSubject();
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($chapter['courseId'],1),'id');
        if ($courseIds) {
            $chapterIds = ArrayToolkit::column($this->getCourseService()->findChaptersByChapterIdAndLockedCourseIds($chapter['id'], $courseIds),'id');
            unset($chapter['id'],$chapter['courseId'],$chapter['pId']);
            foreach ($chapterIds as $chapterId) {
               $this->getCourseService()->editChapter($chapterId,$chapter);
            }
        }
    }

    public function onCourseMemberCreate(ServiceEvent $event)
    {
       $member = $event->getSubject();
       $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($member['courseId'],1),'id');
       unset($member['id']);
       if ($courseIds) {
            foreach ($courseIds as $courseId) {
                $member['courseId'] = $courseId;
                $this->getCourseService()->createMember($member);
            }
        } 
    }

    public function onCourseMemberDelete(ServiceEvent $event)
    {
       $member = $event->getSubject();
       $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($member['courseId'],1),'id');
       if($courseIds) {
            foreach ($courseIds as $courseId) {
                $this->getCourseService()->deleteMemberByCourseIdAndUserId($courseId,$member['userId']);
            }
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

    protected function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File.UploadFileService');
    }

}
