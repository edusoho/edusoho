<?php
namespace Topxia\Service\File\Event;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UploadFileEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.delete'        => 'onCourseDelete',
            //'course.lesson.create' => 'onCourseLessonCreate',
            'course.lesson.delete' => 'onCourseLessonDelete',
            'course.material.create'      => 'onMaterialCreate',
            'course.material.update'      => 'onMaterialUpdate',
            'course.material.delete'      => 'onMaterialDelete'
        );
    }

    public function onCourseDelete(ServiceEvent $event)
    {
        $course = $event->getSubject();
        
        $lessons = $this->getCourseService()->getCourseLessons($course['id']);

        if (!empty($lessons)) {
            $fileIds = ArrayToolkit::column($lessons, "mediaId");

            if (!empty($fileIds)) {
                foreach ($fileIds as $fileId) {
                    $this->getUploadFileService()->waveUploadFile($fileId, 'usedCount', -1);
                }
            }
        }
    }

    public function onCourseLessonCreate(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $argument = $context['argument'];
        $lesson   = $context['lesson'];

        if (in_array($lesson['type'], array('video', 'audio', 'ppt', 'document', 'flash'))) {
            $this->getUploadFileService()->waveUploadFile($lesson['mediaId'], 'usedCount', 1);
        }
    }

    public function onCourseLessonDelete(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $lesson   = $context['lesson'];

        if (!empty($lesson['mediaId'])) {
            $this->getUploadFileService()->waveUploadFile($lesson['mediaId'], 'usedCount', -1);
        }
    }

    public function onMaterialCreate(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $material = $context['material'];

        if(!empty($material['fileId'])){
            $this->getUploadFileService()->waveUploadFile($material['fileId'],'usedCount',1);
        }
    }

    public function onMaterialUpdate(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $argument = $context['argument'];
        $material = $context['material'];

        if ($material['fileId'] != $argument['fileId'] && $argument['fileId']) {
            $this->getUploadFileService()->waveUploadFile($material['fileId'],'usedCount', 1);
            $this->getUploadFileService()->waveUploadFile($argument['fileId'],'usedCount',-1);
        }

    }

    public function onMaterialDelete(ServiceEvent $event)
    {
        $material  = $event->getSubject();

        $file = $this->getUploadFileService()->getFile($material['fileId']);

        if (!$file) {
            return false;
        }

        $this->getUploadFileService()->waveUploadFile($file['id'],'usedCount',-1);

        if (!$this->getUploadFileService()->canManageFile($file['id'])) {
            return false;
        }
        
        if ($file['targetId'] == $material['courseId']) {
            $this->getUploadFileService()->update($material['fileId'], array('targetId' => 0));
        }
    }

    protected function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File.UploadFileService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }
}
