<?php
namespace Mooc\Service\Course\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class CourseEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.update' => 'onCourseUpdate',
        );
    }

    public function onCourseUpdate(ServiceEvent $event)
    {
        $course = $event->getSubject();
        if (isset($course['course'])) {
            $course = $course['course'];
        }
        if (isset($course['endTime']) && strtotime('+1 day', $course['endTime']) > time()) {
            $oldJob = $this->getCrontabService()->getJobByNameAndTargetTypeAndTargetId('GenerateCourseScoreJob', 'course', $course['id']);
            if (!empty($oldJob)) {
                $this->getCrontabService()->deleteJob($oldJob['id']);
            }
            $this->getCrontabService()->createJob(array(
                'name'        => 'GenerateCourseScoreJob',
                'cycle'       => 'once',
                'jobClass'    => 'Mooc\\Service\\Course\\Job\\GenerateCourseScoreJob',
                'jobParams'   => array('courseId' => $course['id']),
                'time'        => $course['endTime'],
                'targetType'  => 'course',
                'targetId'    => $course['id'],
                'createdTime' => time(),
            ));
        }
    }

    protected function getCrontabService()
    {
        return ServiceKernel::instance()->createService('Crontab.CrontabService');
    }
}
