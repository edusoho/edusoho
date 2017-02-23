<?php


namespace Biz\Sms\Event;


use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Crontab\Service\CrontabService;
use Biz\Sms\Service\SmsService;
use Biz\Sms\SmsProcessor\SmsProcessorFactory;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TaskEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            'course.task.publish' => "onTaskPublish"
        );
    }

    public function onTaskPublish(Event $event)
    {
        $task = $event->getSubject();

        if ($task['type'] == 'live') {
            $this->createJob($task, 'task');
            $smsType = 'sms_live_lesson_publish';
        } else {
            $smsType = 'sms_normal_lesson_publish';
        }

        if ($this->getSmsService()->isOpen($smsType)) {
            $processor    = SmsProcessorFactory::create('task');
            $return       = $processor->getUrls($task['id'], $smsType);
            $callbackUrls = $return['urls'];
            $count        = ceil($return['count'] / 1000);
            try {
                $api    = CloudAPIFactory::create('root');
                $result = $api->post("/sms/sendBatch", array('total' => $count, 'callbackUrls' => $callbackUrls));
            } catch (\Exception $e) {
                throw new ServiceException('发送失败！');
            }
        }
    }

    protected function createJob($task, $targetType)
    {
        $daySmsType  = 'sms_live_play_one_day';
        $hourSmsType = 'sms_live_play_one_hour';
        $dayIsOpen   = $this->getSmsService()->isOpen($daySmsType);
        $hourIsOpen  = $this->getSmsService()->isOpen($hourSmsType);

        if ($dayIsOpen && $task['startTime'] >= (time() + 24 * 60 * 60)) {
            $startJob = array(
                'name'            => "SmsSendOneDayJob",
                'cycle'           => 'once',
                'nextExcutedTime' => $task['startTime'] - 24 * 60 * 60,
                'jobClass'        => substr(__NAMESPACE__, 0, -5).'Job\\SmsSendOneDayJob',
                'targetType'      => $targetType,
                'targetId'        => $task['id']
            );
            $startJob = $this->getCrontabService()->createJob($startJob);
        }

        if ($hourIsOpen && $task['startTime'] >= (time() + 60 * 60)) {
            $startJob = array(
                'name'            => "SmsSendOneHourJob",
                'cycle'           => 'once',
                'nextExcutedTime' => $task['startTime'] - 60 * 60,
                'jobClass'        => substr(__NAMESPACE__, 0, -5).'Job\\SmsSendOneHourJob',
                'targetType'      => $targetType,
                'targetId'        => $task['id']
            );
            $startJob = $this->getCrontabService()->createJob($startJob);
        }
    }

    /**
     * @return CrontabService
     */
    protected function getCrontabService()
    {
        return $this->getBiz()->service('Crontab:CrontabService');
    }

    /**
     * @return SmsService
     */
    protected function getSmsService()
    {
        return $this->getBiz()->service('Sms:SmsService');
    }
}