<?php

namespace Biz\Sms\Event;

use AppBundle\Common\StringToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseSetService;
use Biz\Sms\Service\SmsService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestPaperEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'answer.finished' => 'onAnswerFinished',
        ];
    }

    public function onAnswerFinished(Event $event)
    {
        $answerReport = $event->getSubject();
        $answerRecord = $this->getAnswerRecordService()->get($answerReport['answer_record_id']);
        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerReport['answer_scene_id']);

        if ('homework' === $activity['mediaType']) {
            $this->notifyHomeworkResult($activity, $answerRecord);
        } elseif ('testpaper' === $activity['mediaType']) {
            $this->notifyTestpaperResult($activity, $answerRecord);
        }
    }

    protected function notifyTestpaperResult($activity, $answerRecord)
    {
        $smsType = 'sms_testpaper_check';

        if ($this->getSmsService()->isOpen($smsType)) {
            $parameters = [];

            $courseSet = $this->getCourseSetService()->getCourseSet($activity['fromCourseSetId']);

            if (!empty($courseSet)) {
                $courseSet['title'] = StringToolkit::cutter($courseSet['title'], 20, 15, 4);
                $task = $this->getTaskService()->getTaskByCourseIdAndActivityId(
                    $activity['fromCourseId'],
                    $activity['id']
                );
                $parameters['lesson_title'] = '《'.$task['title'].'》的试卷';
                $parameters['course_title'] = '《'.$courseSet['title'].'》';
                $description = $parameters['course_title'].' '.$parameters['lesson_title'].'批阅提醒';
                $userId = $answerRecord['user_id'];
                $this->getSmsService()->smsSend($smsType, [$userId], $description, $parameters);
            }
        }
    }

    protected function notifyHomeworkResult($activity, $answerRecord)
    {
        $smsType = 'sms_homework_check';

        if ($this->getSmsService()->isOpen($smsType)) {
            $parameters = [];

            $courseSet = $this->getCourseSetService()->getCourseSet($activity['fromCourseSetId']);

            if (!empty($courseSet)) {
                $courseSet['title'] = StringToolkit::cutter($courseSet['title'], 20, 15, 4);
                $task = $this->getTaskService()->getTaskByCourseIdAndActivityId(
                    $activity['fromCourseId'],
                    $activity['id']
                );
                $parameters['lesson_title'] = '《'.$task['title'].'》的作业';
                $parameters['course_title'] = '《'.$courseSet['title'].'》';
                $description = $parameters['course_title'].' '.$parameters['lesson_title'].'批阅提醒';
                $userId = $answerRecord['user_id'];
                $this->getSmsService()->smsSend($smsType, [$userId], $description, $parameters);
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

    /**
     * @return AnswerRecordService
     */
    public function getAnswerRecordService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerRecordService');
    }
}
