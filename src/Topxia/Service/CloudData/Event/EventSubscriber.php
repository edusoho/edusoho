<?php
namespace Topxia\Service\Course\Event;

use Topxia\Service\Common\ServiceEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.join' => 'onCourseJoin'
        );
    }

    private $tryTimes = 0;

    public function onCourseJoin(ServiceEvent $event)
    {
        try {
        } catch (\Exception $e) {
            if ($tryTimes == 0) {
                $tryTimes++;
                $this->onCourseJoin($event);
            } else {
                $subject = $event->getSubject();
                $fields  = array(
                    'name'    => $event->getName(),
                    'subject' => $subject
                );
                $this->getCloudDataService()->add($fields);
            }
        }
    }

    protected function getCloudDataService()
    {
        return $this->getServiceKernel()->createService('CloudData.CloudDataService');
    }
}
