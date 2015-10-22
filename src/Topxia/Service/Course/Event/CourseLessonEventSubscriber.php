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
            'course.lesson.publish'=> 'onCourseLessonPublish',
            'course.lesson.unpublish'=> 'onCourseLessonUnpublish',
            'course.lesson_start' => 'onLessonStart',
            'course.lesson_finish' =>'onLessonFinish',
            'course.lesson.replay'=>'onCourseLessonReplay'
        );
    }

    public function onCourseLessonCreate(ServiceEvent $event)
    {
        $lesson = $event->getSubject();

        $courseId = $lesson['courseId'];
        $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($courseId);
        foreach ($classroomIds as  $classroomId) {
            $classroom = $this->getClassroomService()->getClassroom($classroomId);
            $lessonNum = $classroom['lessonNum']+1;
            $this->getClassroomService()->updateClassroom($classroomId, array('lessonNum' => $lessonNum));
        }

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');
        foreach ($courseIds as $courseId) {
            $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($courseId);
            foreach ($classroomIds as  $classroomId) {
                $classroom = $this->getClassroomService()->getClassroom($classroomId);
                $lessonNum = $classroom['lessonNum']+1;
                $this->getClassroomService()->updateClassroom($classroomId, array('lessonNum' => $lessonNum));
            }
        }
        
        $course = $this->getCourseService()->getCourse($lesson['courseId']);
        foreach ($courseIds as $courseId) {
            $this->getCourseService()->editCourse($courseId, array('lessonNum'=>$course['lessonNum']));
        }
        if (!empty($courseIds)){
            $lesson ['parentId'] = $lesson ['id'];
            if($lesson['type'] == 'testpaper'){
                $lockedTarget = '';
                foreach ($courseIds as $courseId) {
                    $lockedTarget .= "'course-".$courseId."',";
                }
                $lockedTarget = "(".trim($lockedTarget,',').")";
                $testpaperIds = ArrayToolkit::column($this->getTestpaperService()->findTestpapersByPIdAndLockedTarget($lesson['mediaId'],$lockedTarget),'id');
            }
            unset($lesson ['id'],$lesson['courseId']);
            foreach ($courseIds as $key=>$courseId)
            {   
                $lesson['courseId'] = $courseId;
                if($lesson['type'] == 'testpaper'){
                    $lesson['mediaId'] = $testpaperIds[$key];
                }
                if(!empty($lesson['mediaId'])){
                    $this->getUploadFileService()->waveUploadFile($lesson['mediaId'], 'usedCount', 1);
                }
                $this->getCourseService()->addLesson($lesson);
            }
        }
    }

    public function onCourseLessonDelete(ServiceEvent $event)
    {
        $context = $event->getSubject();

        $courseId = $context["courseId"];

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');

        if ($courseIds) {
            $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($courseId);
            foreach ($classroomIds as $key => $value) {
                $classroom = $this->getClassroomService()->getClassroom($value);
                $lessonNum = $classroom['lessonNum']-1;
                $this->getClassroomService()->updateClassroom($value, array("lessonNum" => $lessonNum));
            }

            foreach ($courseIds as $courseId) {
                $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($courseId);
                foreach ($classroomIds as $classroomId) {
                    $classroom = $this->getClassroomService()->getClassroom($classroomId);
                    $lessonNum = $classroom['lessonNum']-1;
                    $this->getClassroomService()->updateClassroom($classroomId, array("lessonNum" => $lessonNum));
                }
            }

            $lesson = $context["lesson"];
            if ($courseIds) {
                $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByParentIdAndLockedCourseIds($lesson['id'],$courseIds),'id');
                foreach ($lessonIds as $key=>$lessonId) {
                    $this->getCourseService()->deleteLesson($courseIds[$key], $lessonId);
                }
            }
            $course = $this->getCourseService()->getCourse($lesson['courseId']);
            foreach ($courseIds as $courseId) {
                $this->getCourseService()->editCourse($courseId, array("lessonNum"=>$course['lessonNum']));
            }
            if($lesson['type'] == 'live' && $lesson['replayStatus'] == 'generated'){
               foreach ($lessonIds as $lessonId) {
                 $this->getCourseService()->deleteLessonReplayByLessonId($lessonId);
                } 
            }
            

        }
    }

    public function onCourseLessonUpdate(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($lesson['courseId'],1),'id');
        if ($courseIds) {
            $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByParentIdAndLockedCourseIds($lesson['id'],$courseIds),'id');
            unset($lesson['id'],$lesson['courseId'],$lesson['chapterId'],$lesson['parentId']);
            if($lesson['type'] == 'testpaper'){
                unset($lesson['mediaId']);
            }
            foreach ($courseIds as $key=>$courseId) {
                if(array_key_exists('fields', $lesson) && array_key_exists('mediaId', $lesson) && $lesson['fields']['mediaId'] != $lesson['mediaId']){
                    if(!empty($lesson['fields']['mediaId'])){
                        $this->getUploadFileService()->waveUploadFile($lesson['fields']['mediaId'],'usedCount',-1);
                    }
                    if(!empty($lesson['mediaId'])){
                        $this->getUploadFileService()->waveUploadFile($lesson['mediaId'],'usedCount',1);
                    }
                }
                unset($lesson['fields']);
                $this->getCourseService()->editLesson($lessonIds[$key],$lesson);
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
           $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByParentIdAndLockedCourseIds($lessonId,$courseIds),'id'); 
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
           $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByParentIdAndLockedCourseIds($lessonId,$courseIds),'id'); 
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

    public function onCourseLessonReplay(ServiceEvent $event)
    {
        $courseLessonReplay = $event->getSubject();
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseLessonReplay['courseId'],1),'id');
        $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByParentIdAndLockedCourseIds($courseLessonReplay['lessonId'],$courseIds),'id');
        $courseLessonReplay = array('title'=>$courseLessonReplay['title'],'replayId'=>$courseLessonReplay['replayId'],'userId'=>$courseLessonReplay['userId']);
        foreach ($courseIds as $key=>$courseId) {
            $courseLessonReplay['courseId'] = $courseId;
            $courseLessonReplay['lessonId'] = $lessonIds[$key];
            $courseLessonReplay['createdTime'] = time();
            $this->getCourseService()->addCourseLessonReplay($courseLessonReplay);
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
}
