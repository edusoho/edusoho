<?php
namespace Topxia\Service\Taxonomy\Event;

use Biz\File\Service\UploadFileTagService;
use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceKernel;

class TagEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'tag.delete' => 'onTagDelete',
        );
    }

    public function onTagDelete(Event $event)
    {
      $content = $event->getSubject();
      $this->getUploadFileTagService()->deleteByTagId($content['tagId']);
    }

    /**
     * @return UploadFileTagService
     */
    protected function getUploadFileTagService()
    {
        return ServiceKernel::instance()->createService('File:UploadFileTagService');
    }
}
