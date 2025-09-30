<?php

namespace Biz\Sms\Job;

use Biz\AppLoggerConstant;
use Biz\Sms\Service\SmsService;
use Biz\Sms\SmsType;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Sms\SmsProcessor\SmsProcessorFactory;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class SmsSendJob extends AbstractJob
{
    CONST LIMIT = 1000;

    public function execute()
    {
        $args = $this->args;
        $smsType = $args['smsType'];

        if (!$this->getSmsService()->isOpen($smsType)) {
            return;
        }
        try {
            $targetType = $args['targetType'];
            $targetId = $args['targetId'];
            $start = $args['start'] ?? 0;
            $templateId = $this->getSmsTemplateId($smsType);
            if (empty($templateId)) {
                throw new \Exception("短信模板不存在 {$smsType}");
            }
            $processor = SmsProcessorFactory::create($targetType);
            $params = $processor->getSmsParams($targetId, $smsType);
            $userIds = $processor->searchUserIds($targetId, $smsType, $start, self::LIMIT);
            if (empty($userIds)) {
                return;
            }
            $this->getSmsService()->smsSend($smsType, $userIds, $templateId, $params);
            if (count($userIds) >= self::LIMIT) {
                $start += self::LIMIT;
                $this->registerNextJob($smsType, $targetId, $targetType, $start);
            }

        } catch (\Exception $e) {
            $this->getLogService()->error(AppLoggerConstant::SMS, $smsType, "发送短信通知失败:targetType:{$targetType}, targetId:{$targetId}, start:{$start}", ['error' => $e->getMessage()]);
        }
    }

    private function getSmsTemplateId($smsType)
    {
        switch ($smsType) {
            case 'sms_live_play_one_day':
            case 'sms_live_play_one_hour':
            case 'sms_live_lesson_publish':
                return SmsType::LIVE_NOTIFY;
            case 'sms_normal_lesson_publish':
                return SmsType::TASK_PUBLISH;
        }
        return '';
    }

    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return SmsService
     */
    protected function getSmsService()
    {
        return $this->biz->service('Sms:SmsService');
    }

    private function registerNextJob($smsType, $targetId, $targetType, $start)
    {
        $this->createJob([
            'name' => $this->name . '_'. $start,
            'expression' => time(), // 立即执行
            'class' => self::class,
            'misfire_threshold' => 60 * 60,
            'args' => array(
                'targetType' => $targetType,
                'targetId' => $targetId,
                'start' => $start,
                'smsType' => $smsType,
            ),
        ]);
    }

    private function createJob($startJob)
    {
        $job = $this->getSchedulerService()->getJobByName($startJob['name']);
        if (!isset($job)) {
            $this->getSchedulerService()->register($startJob);
        }
    }

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}
