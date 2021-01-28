<?php

namespace Biz\Sms\Job;

use Biz\AppLoggerConstant;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Sms\SmsProcessor\SmsProcessorFactory;

class SmsSendOneHourJob extends AbstractJob
{
    public function execute()
    {
        $smsType = 'sms_live_play_one_hour';
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
                $result = $api->post('/sms/sendBatch', array('total' => $count, 'callbackUrls' => $callbackUrls));
                $this->getLogService()->info('sms', 'sms-sendbatch', 'callbackUrls', $callbackUrls);
                $this->getLogService()->info('sms', 'sms-sendbatch', 'result', empty($result) ? array() : $result);
            } catch (\Exception $e) {
                $this->getLogService()->error(AppLoggerConstant::SMS, 'sms_live_play_one_hour', "发送短信通知失败:targetType:{$targetType}, targetId:{$targetId}", array('error' => $e->getMessage()));
            }
        }
    }

    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    protected function getSmsService()
    {
        return $this->biz->service('Sms:SmsService');
    }
}
