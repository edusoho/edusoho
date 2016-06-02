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
            'course.delete'          => 'onCourseDelete',
            'course.lesson.create'   => array('onCourseLessonCreate', 0),
            'course.lesson.delete'   => array('onCourseLessonDelete', 0),
            'course.lesson.update'   => 'onCourseLessonUpdate',
            'upload.file.delete'     => 'onUploadFileDelete',
            'course.material.delete' => 'onMaterialDelete',
        );
    }

    public function onCourseDelete(ServiceEvent $event)
    {
        $course = $event->getSubject();
        $this->getMaterialService()->deleteMaterialsByCourseId($course['id']);
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
        $lesson   = $context['lesson'];
        $courseId = $context['courseId'];

        $material = $this->getMaterialService()->searchMaterials(
            array(
                'courseId' => $lesson['courseId'],
                'lessonId' => $lesson['id'],
                'fileId'   => $lesson['mediaId']
            ),
            array('createdTime','DESC'), 0, 1
        );
        if ($material) {
            $updateFields = array(
                'lessonId' => 0
            );
            $this->getMaterialService()->updateMaterial($material[0]['id'], $updateFields, array('fileId'=>$material[0]['fileId']));
        }
        
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
                $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
                $updateFields = array('fileId' => $lesson['mediaId'],'title'=>$file['filename']);
                $this->getMaterialService()->updateMaterial($material[0]['id'], 
                    $updateFields, array('fileId'=>$material[0]['fileId'])
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

    public function onMaterialDelete(ServiceEvent $event)
    {
        $material = $event->getSubject();

        $file = $this->getUploadFileService()->getFile($material['fileId']);

        if (!$file) {
            return false;
        }

        if (!$this->getUploadFileService()->canManageFile($file['id'])) {
            return false;
        }
        
        if ($file['targetId'] == $material['courseId']) {
            $this->getUploadFileService()->update($material['fileId'], array('targetId' => 0));
        }
        
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
