<?php

namespace Biz\File\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\LiveReplayService;
use Biz\File\Service\UploadFileService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceKernel;

class UploadFileEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'question.create' => ['onQuestionCreate', 2],
            'question.update' => ['onQuestionUpdate', 2],
            'question.delete' => ['onQuestionDelete', 2],

            'course.delete' => 'onCourseDelete',

            'course.material.create' => 'onMaterialCreate',
            'course.material.update' => 'onMaterialUpdate',
            'course.material.delete' => 'onMaterialDelete',

            'open.course.lesson.delete' => 'onOpenCourseLessonDelete',
            'open.course.delete' => 'onOpenCourseDelete',

            'article.delete' => 'onArticleDelete',
            'group.thread.post.delete' => 'onGroupThreadPostDelete',
            'group.thread.delete' => 'onGroupThreadDelete',
            'course.thread.delete' => 'onCourseThreadDelete',
            'course.thread.post.delete' => 'onCourseThreadPostDelete',
            'thread.delete' => 'onThreadDelete',
            'thread.post.delete' => 'onThreadPostDelete',

            'live.activity.update' => 'onLiveActivityUpdate',
        ];
    }

    public function onQuestionCreate(Event $event)
    {
        $question = $event->getSubject();

        if (!$event->hasArgument('argument')) {
            return;
        }
        $argument = $event->getArgument('argument');

        if (empty($argument['attachment'])) {
            return;
        }

        $attachment = $argument['attachment'];

        if (isset($attachment['stem'])) {
            $this->getUploadFileService()->createUseFiles($attachment['stem']['fileIds'], $question['id'], $attachment['stem']['targetType'], $attachment['stem']['type']);
        }

        if (isset($attachment['analysis'])) {
            $this->getUploadFileService()->createUseFiles($attachment['analysis']['fileIds'], $question['id'], $attachment['analysis']['targetType'], $attachment['analysis']['type']);
        }
    }

    public function onQuestionUpdate(Event $event)
    {
        $question = $event->getSubject();
        if (!$event->hasArgument('argument')) {
            return;
        }
        $argument = $event->getArgument('argument');

        if (empty($argument['fields']['attachment'])) {
            return;
        }

        $attachment = $argument['fields']['attachment'];

        $this->getUploadFileService()->createUseFiles($attachment['stem']['fileIds'], $question['id'], $attachment['stem']['targetType'], $attachment['stem']['type']);
        $this->getUploadFileService()->createUseFiles($attachment['analysis']['fileIds'], $question['id'], $attachment['analysis']['targetType'], $attachment['analysis']['type']);
    }

    public function onQuestionDelete(Event $event)
    {
        $question = $event->getSubject();

        $this->deleteAttachment('question.stem,question.analysis', $question['id']);
    }

    protected function deleteAttachment($targetType, $targetId)
    {
        $conditions = ['targetId' => $targetId, 'type' => 'attachment'];
        if (false === strpos($targetType, ',')) {
            $conditions['targetType'] = $targetType;
        } else {
            $conditions['targetTypes'] = explode(',', $targetType);
        }

        $attachments = $this->getUploadFileService()->searchUseFiles($conditions);

        if (!$attachments) {
            return true;
        }

        foreach ($attachments as $attachment) {
            $this->getUploadFileService()->deleteUseFile($attachment['id']);
        }
    }

    public function onArticleDelete(Event $event)
    {
        $article = $event->getSubject();
        $this->deleteAttachment('article', $article['id']);
    }

    public function onGroupThreadPostDelete(Event $event)
    {
        $threadPost = $event->getSubject();
        $this->deleteAttachment('group.thread.post', $threadPost['id']);
    }

    public function onGroupThreadDelete(Event $event)
    {
        $thread = $event->getSubject();
        $this->deleteAttachment('group.thread', $thread['id']);
    }

    public function onCourseThreadDelete(Event $event)
    {
        $thread = $event->getSubject();
        $this->deleteAttachment('course.thread', $thread['id']);
    }

    public function onCourseThreadPostDelete(Event $event)
    {
        $threadPost = $event->getSubject();
        $this->deleteAttachment('course.thread.post', $threadPost['id']);
    }

    public function onThreadDelete(Event $event)
    {
        $thread = $event->getSubject();
        if (!empty($thread['targetType'])) {
            $this->deleteAttachment($thread['targetType'].'.thread', $thread['id']);
        }
    }

    public function onThreadPostDelete(Event $event)
    {
        $threadPost = $event->getSubject();
        if (!empty($threadPost['targetType'])) {
            $this->deleteAttachment($threadPost['targetType'].'.thread.post', $threadPost['id']);
        }
    }

    public function onCourseDelete(Event $event)
    {
        $course = $event->getSubject();
    }

    public function onCourseLessonCreate(Event $event)
    {
        $context = $event->getSubject();
        $lesson = $context['lesson'];

        if (in_array($lesson['type'], ['video', 'audio', 'ppt', 'document', 'flash'])) {
            $this->getUploadFileService()->waveUsedCount($lesson['mediaId'], 1);
        }
    }

    public function onCourseLessonDelete(Event $event)
    {
        $lesson = $event->getSubject();

        if (!empty($lesson['mediaId'])) {
            $this->getUploadFileService()->waveUsedCount($lesson['mediaId'], -1);
        }
    }

    public function onMaterialCreate(Event $event)
    {
        $material = $event->getSubject();

        if (!empty($material['fileId'])) {
            $this->getUploadFileService()->waveUsedCount($material['fileId'], 1);
        }
    }

    public function onMaterialUpdate(Event $event)
    {
        $material = $event->getSubject();
        $argument = $event->getArgument('argument');
        $sourceMaterial = $event->getArgument('sourceMaterial');

        if (!$material['lessonId'] && $sourceMaterial['lessonId']) {
            $this->getUploadFileService()->waveUsedCount($material['fileId'], -1);
        } elseif ($material['fileId'] != $argument['fileId'] && $argument['fileId']) {
            $this->getUploadFileService()->waveUsedCount($material['fileId'], 1);
            $this->getUploadFileService()->waveUsedCount($argument['fileId'], -1);
        } elseif (!$sourceMaterial['lessonId'] && $material['lessonId']) {
            $this->getUploadFileService()->waveUsedCount($material['fileId'], 1);
        }
    }

    public function onMaterialDelete(Event $event)
    {
        $material = $event->getSubject();

        $file = $this->getUploadFileService()->getFile($material['fileId']);

        if (!$file) {
            return;
        }

        $this->getUploadFileService()->waveUsedCount($file['id'], -1);

        if (!$this->getUploadFileService()->canManageFile($file['id'])) {
            return;
        }

        if ($file['targetId'] == $material['courseId']) {
            $this->getUploadFileService()->update($material['fileId'], ['targetId' => 0]);
        }
    }

    public function onOpenCourseLessonDelete(Event $event)
    {
        $context = $event->getSubject();
        $lesson = $context['lesson'];

        if (!empty($lesson['mediaId'])) {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
            if ($file['usedCount'] > 0) {
                $this->getUploadFileService()->waveUsedCount($lesson['mediaId'], -1);
            }
        }
    }

    public function onOpenCourseDelete(Event $event)
    {
        $course = $event->getSubject();

        $lessons = $this->getOpenCourseService()->findLessonsByCourseId($course['id']);

        if (!empty($lessons)) {
            $fileIds = ArrayToolkit::column($lessons, 'mediaId');

            if (!empty($fileIds)) {
                foreach ($fileIds as $fileId) {
                    $this->getUploadFileService()->waveUsedCount($fileId, -1);
                }
            }
        }
    }

    public function onLiveActivityUpdate(Event $event)
    {
        $liveActivity = $event->getSubject();
        $fields = $event->getArgument('fields');

        if (isset($fields['replayStatus']) && LiveReplayService::REPLAY_VIDEO_GENERATE_STATUS == $fields['replayStatus']) {
            $this->getUploadFileService()->waveUsedCount($fields['mediaId'], 1);
        }
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File:UploadFileService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse:OpenCourseService');
    }

    protected function getQuestionService()
    {
        return $this->getBiz()->service('Question:QuestionService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
