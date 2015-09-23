<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/23
 * Time: 15:58
 */

namespace Custom\Service\School\Event;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class SchoolEventSubscriber extends ServiceEvent implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'school.delete' => 'onSchoolDelete'
        );
    }

    public function onSchoolDelete(ServiceEvent $event)
    {
        $schoolOrganizationId = $event->getSubject();

        $this->getUserService()->resetUserSchoolOrganizationId($schoolOrganizationId);

    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

}