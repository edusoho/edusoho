<?php


namespace Biz\Sms\Event;


use AppBundle\Common\StringToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseSetService;
use Biz\Crontab\Service\CrontabService;
use Biz\Sms\Service\SmsService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestPaperEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            'testpaper.reviewed' => 'onTestpaperReviewed'
        );
    }

    public function onTestpaperReviewed(Event $event)
    {
        $parameters = array();
        $smsType    = 'sms_testpaper_check';

        if ($this->getSmsService()->isOpen($smsType)) {
            $paperResult = $event->getSubject();
            $courseSet   = $this->getCourseSetService()->getCourseSet($paperResult['courseSetId']);

            if (!empty($courseSet)) {
                $courseSet['title']         = StringToolkit::cutter($courseSet['title'], 20, 15, 4);
                $task                       = $this->getTaskService()->getTaskByCourseIdAndActivityId($paperResult['courseId'], $paperResult['lessonId']);
                $parameters['lesson_title'] = '《' . $task['title'] . '》' . '(试卷)';
                $parameters['course_title'] = '《' . $courseSet['title'] . '》';
                $description                = $parameters['course_title'] . ' ' . $parameters['lesson_title'] . '试卷批阅提醒';
                $userId                     = $paperResult['userId'];
                $this->getSmsService()->smsSend($smsType, array($userId), $description, $parameters);
            }
        }
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return CrontabService
     */
    protected function getCrontabService()
    {
        return $this->getBiz()->service('Crontab:CrontabService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return SmsService
     */
    protected function getSmsService()
    {
        return $this->getBiz()->service('Sms:SmsService');
    }

}