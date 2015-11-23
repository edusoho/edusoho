<?php
namespace Custom\Service\User\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceEvent;

class OrganizationEventSubscriber extends ServiceEvent implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'organization.delete' => 'onOrganizationDelete'
        );
    }

    public function onOrganizationDelete(ServiceEvent $event)
    {
        $organizationId = $event->getSubject();

        $this->getUserService()->resetUserOrganizationId($organizationId);

    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

}
