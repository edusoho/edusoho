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
            'course.delete'             => 'onCourseDelete',
            //'course.lesson.create' => 'onCourseLessonCreate',
            'course.lesson.delete'      => 'onCourseLessonDelete',
            'course.material.create'    => 'onMaterialCreate',
            'course.material.update'    => 'onMaterialUpdate',
            'course.material.delete'    => 'onMaterialDelete',

            'open.course.lesson.delete' => 'onOpenCourseLessonDelete',
            'open.course.delete'        => 'onOpenCourseDelete',

            'article.delete'            => 'onArticleDelete',
            'question.delete'           => 'onQuestionDelete',
            'group.thread.post.delete'  => 'onGroupThreadPostDelete',
            'group.thread.delete'       => 'onGroupThreadDelete',
            'course.thread.delete'      => 'onCourseThreadDelete',
            'course.thread.post.delete' => 'onCourseThreadPostDelete',
            'thread.delete'             => 'onThreadDelete',
            'thread.post.delete'        => 'onThreadPostDelete'
        );
    }

    public function onArticleDelete(ServiceEvent $event)
    {
        $article = $event->getSubject();
        $this->deleteAttachment('article', $article['id']);
    }

    public function onQuestionDelete(ServiceEvent $event)
    {
        $question = $event->getSubject();
        $this->deleteAttachment('question.stem,question.analysis', $question['id']);
    }

    public function onGroupThreadPostDelete(ServiceEvent $event)
    {
        $threadPost = $event->getSubject();
        $this->deleteAttachment('group.thread.post', $threadPost['id']);
    }

    public function onGroupThreadDelete(ServiceEvent $event)
    {
        $thread = $event->getSubject();
        $this->deleteAttachment('group.thread', $thread['id']);
    }

    /**
     * [onCourseThreadPostDelete description]
     * @param  ServiceEvent $event          [description]
     * @return [type]       [description]
     */
    public function onCourseThreadDelete(ServiceEvent $event)
    {
        $thread = $event->getSubject();
        $this->deleteAttachment('course.thread', $thread['id']);
    }

    public function onCourseThreadPostDelete(ServiceEvent $event)
    {
        $threadPost = $event->getSubject();
        $this->deleteAttachment('course.thread.post', $threadPost['id']);
    }

    /**
     * [onThreadPostDelete description]
     * @param  ServiceEvent $event          [description]
     * @return [type]       [description]
     */
    public function onThreadDelete(ServiceEvent $event)
    {
        $thread = $event->getSubject();
        if (!empty($thread['targetType'])) {
            $this->deleteAttachment($thread['targetType'].'.thread', $thread['id']);
        }
    }

    public function onThreadPostDelete(ServiceEvent $event)
    {
        $threadPost = $event->getSubject();
        if (!empty($threadPost['targetType'])) {
            $this->deleteAttachment($threadPost['targetType'].'.thread.post', $threadPost['id']);
        }
    }

    private function deleteAttachment($targetType, $targetId)
    {
        $conditions = array('targetId' => $targetId, 'type' => 'attachment');
        if (strpos($targetType, ',') === false) {
            $conditions['targetType'] = $targetType;
        } else {
            $conditions['targetTypes'] = explode(',', $targetType);
        }

        $attachments = $this->getUploadFileService()->searchUseFiles($conditions);
        foreach ($attachments as $attachment) {
            $this->getUploadFileService()->deleteUseFile($attachment['id']);
        }
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
        $context = $event->getSubject();
        $lesson  = $context['lesson'];

        if (!empty($lesson['mediaId'])) {
            $this->getUploadFileService()->waveUploadFile($lesson['mediaId'], 'usedCount', -1);
        }
    }

    public function onMaterialCreate(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $material = $context['material'];

        if (!empty($material['fileId'])) {
            $this->getUploadFileService()->waveUploadFile($material['fileId'], 'usedCount', 1);
        }
    }

    public function onMaterialUpdate(ServiceEvent $event)
    {
        $context        = $event->getSubject();
        $argument       = $context['argument'];
        $material       = $context['material'];
        $sourceMaterial = $context['sourceMaterial'];

        if (!$material['lessonId'] && $sourceMaterial['lessonId']) {
            $this->getUploadFileService()->waveUploadFile($material['fileId'], 'usedCount', -1);
        } elseif ($material['fileId'] != $argument['fileId'] && $argument['fileId']) {
            $this->getUploadFileService()->waveUploadFile($material['fileId'], 'usedCount', 1);
            $this->getUploadFileService()->waveUploadFile($argument['fileId'], 'usedCount', -1);
        } elseif (!$sourceMaterial['lessonId'] && $material['lessonId']) {
            $this->getUploadFileService()->waveUploadFile($material['fileId'], 'usedCount', 1);
        }
    }

    public function onMaterialDelete(ServiceEvent $event)
    {
        $material = $event->getSubject();

        $file = $this->getUploadFileService()->getFile($material['fileId']);

        if (!$file) {
            return false;
        }

        $this->getUploadFileService()->waveUploadFile($file['id'], 'usedCount', -1);

        if (!$this->getUploadFileService()->canManageFile($file['id'])) {
            return false;
        }

        if ($file['targetId'] == $material['courseId']) {
            $this->getUploadFileService()->update($material['fileId'], array('targetId' => 0));
        }
    }

    public function onOpenCourseLessonDelete(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $lesson  = $context['lesson'];

        if (!empty($lesson['mediaId'])) {
            $this->getUploadFileService()->waveUploadFile($lesson['mediaId'], 'usedCount', -1);
        }
    }

    public function onOpenCourseDelete(ServiceEvent $event)
    {
        $course = $event->getSubject();

        $lessons = $this->getOpenCourseService()->findLessonsByCourseId($course['id']);

        if (!empty($lessons)) {
            $fileIds = ArrayToolkit::column($lessons, "mediaId");

            if (!empty($fileIds)) {
                foreach ($fileIds as $fileId) {
                    $this->getUploadFileService()->waveUploadFile($fileId, 'usedCount', -1);
                }
            }
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

    protected function getOpenCourseService()
    {
        return ServiceKernel::instance()->createService('OpenCourse.OpenCourseService');
    }
}
