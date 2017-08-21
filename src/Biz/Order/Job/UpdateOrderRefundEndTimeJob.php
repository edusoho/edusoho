<?php

namespace Biz\Order\Job;

use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class UpdateOrderRefundEndTimeJob extends AbstractJob
{
    public function execute()
    {
        $refundSetting = $this->getSettingService()->get('refund', array());
        $currentMaxRefundDays = empty($refundSetting['maxRefundDays']) ? 0 : $refundSetting['maxRefundDays'];
        $currentMaxRefundTimes = $currentMaxRefundDays * 86400;

        $sql = "UPDATE `orders` SET refundEndTime = (paidTime + {$currentMaxRefundTimes}) WHERE status = 'paid' AND refundEndTime = 0";
        $this->biz['db']->exec($sql);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }




}
