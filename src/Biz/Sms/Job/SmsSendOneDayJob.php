<?php

namespace Biz\Sms\Job;

use Biz\AppLoggerConstant;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Sms\Service\SmsService;
use Biz\Sms\SmsProcessor\SmsProcessorFactory;
use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class SmsSendOneDayJob extends AbstractJob
{
    public function execute()
    {
        $smsType = 'sms_live_play_one_day';
        $dayIsOpen = $this->getSmsService()->isOpen($smsType);

        if ($dayIsOpen) {
            try {
                $targetType = $this->args['targetType'];
                $targetId = $this->args['targetId'];
                $processor = SmsProcessorFactory::create($targetType);
                $return = $processor->getUrls($targetId, $smsType);
                $callbackUrls = $return['urls'];
                $count = ceil($return['count'] / 1000);

                $api = CloudAPIFactory::create('leaf');
                $api->post('/sms/sendBatch', ['total' => $count, 'callbackUrls' => $callbackUrls]);
            } catch (\Exception $e) {
                $this->getLogService()->error(AppLoggerConstant::SMS, 'sms_live_play_one_day', "发送短信通知失败:targetType:{$targetType}, targetId:{$targetId}", ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * @return LogService
     */
    private function getLogService()
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
}
