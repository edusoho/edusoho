<?php
namespace Topxia\Service\Taxonomy\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class TagOwnerEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'tagOwner.delete' => 'onTagOwnerDelete',
        );
    }

    public function onTagOwnerDelete(ServiceEvent $event)
    {

        $fields = $event->getSubject();

        $owner = array(
            'ownerType' => $fields['ownerType'],
            'ownerId'   => $fields['ownerId']
        );

        $this->getTagService()->deleteTagOwnerRelationsByOwner($owner);
    }

    protected function getTagService()
    {
        return ServiceKernel::instance()->createService('Taxonomy.TagService');
    }
}
