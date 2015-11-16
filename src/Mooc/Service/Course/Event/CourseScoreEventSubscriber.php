<?php
namespace Mooc\service\Course\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class CourseScoreEventSubscriber implements EventSubscriberInterface
{
    const TARGETTYPE = "CourseScore";
    public static function getSubscribedEvents()
    {
        return array(
            'scoreSetting.add'    => 'onCourseScoreAdd',
            'scoreSetting.update' => 'onCourseScoreUpdate'
        );
    }

    public function onCourseScoreAdd(ServiceEvent $event)
    {
        $courseScoreSetting = $event->getSubject();

        if ('auto' == $courseScoreSetting['publishType']) {
            $task = $this->generateTask($courseScoreSetting);
            $this->getCrontabService()->createJob($task);
        }
    }

    public function onCourseScoreUpdate(ServiceEvent $event)
    {
        $courseScoreSetting = $event->getSubject();
        $task               = $this->getCrontabJobService()->findJobByTargetTypeAndTargetId(self::TARGETTYPE, $courseScoreSetting['courseId']);

        if (count($task)) {
            $this->getCrontabService()->deleteJob($task[0]['id']);
        }

        if ('auto' == $courseScoreSetting['publishType']) {
            $task = $this->generateTask($courseScoreSetting);
            $this->getCrontabService()->createJob($task);
        }
    }

    private function generateTask($courseScoreSetting)
    {
        return array(
            'name'               => 'publishCourseScore',
            'cycle'              => 'once',
            'cycleTime'          => strtotime($courseScoreSetting['expectPublishTime']),
            'jobClass'           => 'Mooc\\Service\\Course\\Job\\PublishCourseScore',
            'jobParams'          => array('courseId' => $courseScoreSetting['courseId']),
            'executing'          => '0',
            'nextExcutedTime'    => '0',
            'time'               => $courseScoreSetting['expectPublishTime'],
            'latestExecutedTime' => '0',
            'targetType'         => self::TARGETTYPE,
            'targetId'           => $courseScoreSetting['courseId']
        );
    }

    protected function getCrontabService()
    {
        return ServiceKernel::instance()->createService("Crontab.CrontabService");
    }

    protected function getCrontabJobService()
    {
        return serviceKernel::instance()->createService("Mooc:CrontabJob.CrontabJobService");
    }
}
