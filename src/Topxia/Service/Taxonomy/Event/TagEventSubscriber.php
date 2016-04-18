<?php
namespace Topxia\Service\Taxonomy\Event;

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

    public function onTagDelete(ServiceEvent $event)
    {
      $tagId = $event->getArgument('tagId');
      $this->getUploadFileTagService()->deleteByTagId($tagId);
    }
    
    protected function getUploadFileTagService()
    {
        return ServiceKernel::instance()->createService('File.UploadFileTagService');
    }
}
