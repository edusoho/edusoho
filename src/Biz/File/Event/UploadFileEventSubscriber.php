<?php
namespace Biz\File\Event;

use Codeages\Biz\Framework\Event\Event;
use Topxia\Service\Common\ServiceKernel;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UploadFileEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'question.create' => 'onQuestionCreate',
            'question.update' => 'onQuestionUpdate',
            'question.delete' => 'onQuestionDelete'
        );
    }

    public function onQuestionCreate(Event $event)
    {
        $question   = $event->getSubject();
        $argument   = $event->getArgument('argument');
        $attachment = $argument['attachment'];

        if (empty($attachment)) {
            return false;
        }

        $this->getUploadFileService()->createUseFiles($attachment['stem']['fileIds'], $question['id'], $attachment['stem']['targetType'], $attachment['stem']['type']);
        $this->getUploadFileService()->createUseFiles($attachment['analysis']['fileIds'], $question['id'], $attachment['analysis']['targetType'], $attachment['analysis']['type']);
    }

    public function onQuestionUpdate(Event $event)
    {
        $question   = $event->getSubject();
        $argument   = $event->getArgument('argument');
        $attachment = $argument['fields']['attachment'];

        if (empty($attachment)) {
            return false;
        }

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

    protected function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File.UploadFileService');
    }
}
