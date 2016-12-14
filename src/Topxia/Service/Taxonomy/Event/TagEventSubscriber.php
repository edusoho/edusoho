<?php
namespace Topxia\Service\Taxonomy\Event;

use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;
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

    protected function getUploadFileTagService()
    {
        return ServiceKernel::instance()->createService('File.UploadFileTagService');
    }
}
