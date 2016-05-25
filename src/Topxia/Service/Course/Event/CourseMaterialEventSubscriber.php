<?php
namespace Topxia\Service\Course\Event;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseMaterialEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.lesson.create' => array('onCourseLessonCreate', 0),
            'course.lesson.delete' => array('onCourseLessonDelete', 0),
            'course.lesson.update' => 'onCourseLessonUpdate',
            'upload.file.delete'   => 'onUploadFileDelete'
        );
    }

    public function onCourseLessonCreate(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $argument = $context['argument'];
        $lesson   = $context['lesson'];

        if ($lesson['type'] == 'testpaper' || !$lesson['mediaId']) {
            return false;
        }

        $material = $this->getMaterialService()->searchMaterials(
            array(
                'courseId' => $lesson['courseId'],
                'lessonId' => $lesson['id'],
                'fileId'   => $lesson['mediaId'],
                'source'   => 'courselesson'
            ),
            array('createdTime','DESC'), 0, 1
        );

        if (!$material) {

            $fields = array(
                'courseId' => $lesson['courseId'],
                'lessonId' => $lesson['id'],
                'fileId'   => $lesson['mediaId'],
                'source'   => 'courselesson'
            );
            $this->getMaterialService()->uploadMaterial($fields);
            
        }
    }

    public function onCourseLessonDelete(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $lesson   = $context["lesson"];
        $courseId = $context["courseId"];

        $this->getMaterialService()->deleteMaterialsByLessonId($lesson['id']);
    }

    public function onCourseLessonUpdate(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $argument = $context['argument'];
        $lesson   = $context['lesson'];

        if ($lesson['type'] == 'testpaper' || !$lesson['mediaId']) {
            return false;
        }

        $material = $this->getMaterialService()->searchMaterials(
            array(
                'courseId' => $lesson['courseId'],
                'lessonId' => $lesson['id'],
                'source'   => 'courselesson'
            ),
            array('createdTime','DESC'), 0, 1
        );

        if ($material) {
            if ($material[0]['fileId'] != $lesson['mediaId']) {
                $this->getMaterialService()->updateMaterial($material[0]['id'], 
                    array('fileId' => $lesson['mediaId'])
                );
            }
        } else {
            $fields = array(
                'courseId' => $lesson['courseId'],
                'lessonId' => $lesson['id'],
                'fileId'   => $lesson['mediaId'],
                'source'   => 'courselesson'
            );
            $this->getMaterialService()->uploadMaterial($fields);
        }
        
    }

    public function onUploadFileDelete(ServiceEvent $event)
    {
        $file = $event->getSubject();
        $this->getMaterialService()->deleteMaterialsByFileId($file['id']);
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
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
