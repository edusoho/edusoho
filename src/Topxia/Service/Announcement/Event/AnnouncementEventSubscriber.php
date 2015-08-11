<?php
namespace Topxia\Service\Announcement\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\WebBundle\Util\TargetHelper;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Util\EdusohoTuiClient;

class AnnouncementEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'announcement.service.create' => 'onAnnouncementCreated',
        );
    }

    public function onAnnouncementCreated(ServiceEvent $event)
    {
        $announcement = $event->getSubject();
        if ($announcement['targetType'] == 'global') {
            $tuiClient = new EdusohoTuiClient();
            $result = $tuiClient->sendAnnouncement($announcement);
        }
    }
}
