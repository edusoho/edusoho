<?php
namespace Topxia\Service\Course\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;

class CourseLessonEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'course.lesson.create' => array('onCourseLessonCreate', 0),
            'course.lesson.delete' => array('onCourseLessonDelete', 0),           
            'course.lesson.update'=> 'onCourseLessonUpdate',
            'course.lesson.generate.replay'=>'onCourseLessonGenerateReplay',
            'course.lesson.publish'=> 'onCourseLessonPublish',
            'course.lesson.unpublish'=> 'onCourseLessonUnpublish',
            'course.lesson_start' => 'onLessonStart',
            'course.lesson_finish' =>'onLessonFinish',
            'material.create' => 'onMaterialCreate',
            'material.delete' => 'onMaterialDelete',
            'chapter.create' => 'onChapterCreate',
            'chapter.delete' => 'onChapterDelete',
            'chapter.update' => 'onChapterUpdate'
        );
    }

    public function onCourseLessonCreate(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $argument = $context['argument'];
        $lesson = $context['lesson'];

        $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($lesson['courseId']);
        foreach ($classroomIds as  $classroomId) {
            $classroom = $this->getClassroomService()->getClassroom($classroomId);
            $lessonNum = $classroom['lessonNum']+1;
            $this->getClassroomService()->updateClassroom($classroomId, array('lessonNum' => $lessonNum));
        }

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($lesson['courseId'],1),'id');
        if($courseIds){
            $argument['copyId'] = $lesson['id'];
            if(array_key_exists('type',$argument) && $argument['type'] == 'testpaper'){
                $lockedTarget = '';
                foreach ($courseIds as $courseId) {
                        $lockedTarget .= "'course-".$courseId."',";
                }
                $lockedTarget = "(".trim($lockedTarget,',').")";
                $testpaperIds = ArrayToolkit::column($this->getTestpaperService()->findTestpapersByCopyIdAndLockedTarget($argument['mediaId'],$lockedTarget),'id'); 
            }
            foreach ($courseIds as $key=>$courseId)
            {   
                if(array_key_exists('type',$argument) && $argument['type'] == 'testpaper'){
                    $argument['mediaId']=$testpaperIds[$key];
                }
                $argument['courseId']=$courseId;
                $this->getCourseService()->createLesson($argument);
            }
        }
        

    }

    public function onCourseLessonDelete(ServiceEvent $event)
    {
        $context = $event->getSubject();

        $courseId = $context["courseId"];

        $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($courseId);
        foreach ($classroomIds as $key => $value) {
            $classroom = $this->getClassroomService()->getClassroom($value);
            $lessonNum = $classroom['lessonNum']-1;
            $this->getClassroomService()->updateClassroom($value, array("lessonNum" => $lessonNum));
        }

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');

        if ($courseIds) {
            $lesson = $context["lesson"];
            $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($lesson['id'],$courseIds),'id');
            foreach ($lessonIds as $key=>$lessonId) {
                $this->getCourseService()->deleteLesson($courseIds[$key], $lessonId);
            }
        }
    }

    public function onCourseLessonGenerateReplay(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($context['courseId'],1),'id');
        if($courseIds){
            $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($context['lessonId'],$courseIds),'id');
            foreach ($courseIds as $key => $courseId) {
                $this->getCourseService()->generateLessonReplay($courseId,$lessonIds[$key]);
            }
        }
    }

    public function onCourseLessonUpdate(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $argument = $context['argument'];
        $lesson = $context['lesson'];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($lesson['courseId'],1),'id');
        if ($courseIds) {
            $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($lesson['id'],$courseIds),'id');
            foreach ($courseIds as $key=>$courseId) {
                $this->getCourseService()->updateLesson($courseId,$lessonIds[$key],$argument);
            } 
        }
    }

    public function onCourseLessonPublish(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $courseId = $lesson["courseId"];
        $lessonId = $lesson["id"];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');
        if($courseIds){
           $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($lessonId,$courseIds),'id'); 
            foreach ($courseIds as $key=>$courseId) {
                $this->getCourseService()->publishLesson($courseId,$lessonIds[$key]);
            }
        }
    }

    public function onCourseLessonUnpublish(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $courseId = $lesson["courseId"];
        $lessonId = $lesson["id"];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');
        if($courseIds){
           $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($lessonId,$courseIds),'id'); 
            foreach ($courseIds as $key=>$courseId) {
                $this->getCourseService()->unpublishLesson($courseId,$lessonIds[$key]);
            }
        }

    }

    public function onLessonStart(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $course = $event->getArgument('course');
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
            'type' => 'start_learn_lesson',
            'courseId' => $course['id'],
            'objectType' => 'lesson',
            'objectId' => $lesson['id'],
            'private' => $private,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
                'lesson' => $this->simplifyLesson($lesson),
            ),
        ));
    }

    public function onLessonFinish(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $course = $event->getArgument('course');
        $private = $course['status'] == 'published' ? 0 :1;
        if($course['parentId']){ 
            $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']); 
            $classroom = $this->getClassroomService()->getClassroom($classroom['classroomId']);
            if(array_key_exists('showable',$classroom) && $classroom['showable']== 1) {
                $private = 0;
            }else{
                $private = 1;
            }
        }
        $this->getStatusService()->publishStatus(array(
            'type' => 'learned_lesson',
            'courseId' => $course['id'],
            'objectType' => 'lesson',
            'objectId' => $lesson['id'],
            'private' => $private,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
                'lesson' => $this->simplifyLesson($lesson),
            ),
        ));
    }

    public function onMaterialCreate(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $argument = $context['argument'];
        $material = $context['material'];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($material['courseId'],1),'id');
        if ($courseIds) {
            $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($material['lessonId'],$courseIds),'id');
            $argument['copyId'] = $material['id'];
            foreach ($courseIds as $key => $courseId) {
                $argument['courseId'] = $courseId;
                $argument['lessonId'] = $lessonIds[$key];
                $this->getMaterialService()->uploadMaterial($argument);
            }
        }
    }

    public function onMaterialDelete(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($context['courseId'],1),'id');
        if ($courseIds) {
            $materialIds = ArrayToolkit::column($this->getMaterialService()->findMaterialsByCopyIdAndLockedCourseIds($context['id'],$courseIds),'id');
            foreach ($materialIds as $key=>$materialId) {
                $this->getMaterialService()->deleteMaterial($courseIds[$key],$materialId);
            }
        }
    }

    public function onChapterCreate(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $argument = $context['argument'];
        $chapter = $context['chapter'];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($chapter['courseId'],1),'id');
        if ($courseIds){
            $argument['copyId'] = $chapter['id'];
            foreach ($courseIds as  $courseId) {
                $argument['courseId'] = $courseId;
                $this->getCourseService()->createChapter($argument);
            }
        }
    }

    public function onChapterDelete(ServiceEvent $event)
    {
        $chapter = $event->getSubject();
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($chapter['courseId'],1),'id');
        if ($courseIds) {
           $chapterIds = ArrayToolkit::column($this->getCourseService()->findChaptersByCopyIdAndLockedCourseIds($chapter['id'], $courseIds),'id');
           foreach ($chapterIds as $key=>$chapterId) {
               $this->getCourseService()->deleteChapter($courseIds[$key],$chapterId);
           }
        }
        
    }

    public function onChapterUpdate(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $argument = $context['argument'];
        $chapter = $context['chapter'];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($chapter['courseId'],1),'id');
        if ($courseIds) {
            $chapterIds = ArrayToolkit::column($this->getCourseService()->findChaptersByCopyIdAndLockedCourseIds($chapter['id'], $courseIds),'id');
            foreach ($chapterIds as $key=>$chapterId) {
               $argument['courseId']=$courseIds[$key];
               $this->getCourseService()->updateChapter($courseIds[$key], $chapterId, $argument);
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

    private function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getTestpaperService()
    {
        return ServiceKernel::instance()->createService('Testpaper.TestpaperService');
    }

    protected function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File.UploadFileService');
    }
    
    protected function getMaterialService()
    {
        return ServiceKernel::instance()->createService('Course.MaterialService');
    }
}
