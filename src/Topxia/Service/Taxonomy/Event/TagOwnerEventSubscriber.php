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
            'tagOwner.alert'  => 'onTagOwnerAlert'
        );
    }

    public function onTagOwnerAlert(ServiceEvent $event)
    {
        $fields = $event->getSubject();

        $user   = $fields['user'];
        $owner  = $fields['owner'];
        $tagIds = $fields['tagIds'];
        $type   = $fields['type'];

        if ($type == 'update') {
            $this->getTagService()->deleteTagOwnerRelationByOwner($owner);
        }

        foreach ($tagIds as $tagId) {
            $this->getTagService()->addTagOwnerRelation(array(
                'ownerType'   => $owner['ownerType'],
                'ownerId'     => $owner['ownerId'],
                'tagId'       => $tagId,
                'userId'      => $user['id'],
                'createdTime' => time()
            ));
        }
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
