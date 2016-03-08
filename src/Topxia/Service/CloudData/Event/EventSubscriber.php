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
            }
        }
    }

    protected function getDataReportService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
