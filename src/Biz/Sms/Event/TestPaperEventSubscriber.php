<?php

namespace Biz\Sms\Event;

use AppBundle\Common\StringToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseSetService;
use Biz\Sms\Service\SmsService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestPaperEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'exam.reviewed' => 'onExamReviewed',
        );
    }

    public function onExamReviewed(Event $event)
    {
        $paperResult = $event->getSubject();

        if ($paperResult['type'] === 'homework') {
            $this->notifyHomeworkResult($paperResult);
        } elseif ($paperResult['type'] === 'testpaper') {
            $this->notifyTestpaperResult($paperResult);
        }
    }

    protected function notifyTestpaperResult($result)
    {
        $smsType = 'sms_testpaper_check';

        if ($this->getSmsService()->isOpen($smsType)) {
            $parameters = array();

            $courseSet = $this->getCourseSetService()->getCourseSet($result['courseSetId']);

            if (!empty($courseSet)) {
                $courseSet['title'] = StringToolkit::cutter($courseSet['title'], 20, 15, 4);
                $task = $this->getTaskService()->getTaskByCourseIdAndActivityId(
                    $result['courseId'],
                    $result['lessonId']
                );
                $parameters['lesson_title'] = '《'.$task['title'].'》的试卷';
                $parameters['course_title'] = '《'.$courseSet['title'].'》';
                $description = $parameters['course_title'].' '.$parameters['lesson_title'].'批阅提醒';
                $userId = $result['userId'];
                $this->getSmsService()->smsSend($smsType, array($userId), $description, $parameters);
            }
        }
    }

    protected function notifyHomeworkResult($result)
    {
        $smsType = 'sms_homework_check';

        if ($this->getSmsService()->isOpen($smsType)) {
            $parameters = array();

            $courseSet = $this->getCourseSetService()->getCourseSet($result['courseSetId']);

            if (!empty($courseSet)) {
                $courseSet['title'] = StringToolkit::cutter($courseSet['title'], 20, 15, 4);
                $task = $this->getTaskService()->getTaskByCourseIdAndActivityId(
                    $result['courseId'],
                    $result['lessonId']
                );
                $parameters['lesson_title'] = '《'.$task['title'].'》的作业';
                $parameters['course_title'] = '《'.$courseSet['title'].'》';
                $description = $parameters['course_title'].' '.$parameters['lesson_title'].'批阅提醒';
                $userId = $result['userId'];
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
