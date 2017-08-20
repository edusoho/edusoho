<?php

namespace Biz\Order\Job;

use AppBundle\Common\ExceptionPrintingToolkit;
use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class UpdateOrderRefundEndTimeJob extends AbstractJob
{
    public function execute()
    {
        try{
            $refundSetting = $this->getSettingService()->get('refund', array());
            $currentMaxRefundDays = empty($refundSetting['maxRefundDays']) ? 0 : $refundSetting['maxRefundDays'];
            $currentMaxRefundTimes = $currentMaxRefundDays * 86400;

            $sql = "UPDATE `orders` SET refundEndTime = (paidTime + {$currentMaxRefundTimes}) WHERE status = 'paid' AND refundEndTime = 0";
            $this->biz['db']->exec($sql);
            $this->getLogService()->error('order', 'update_refund_end_time', 'order.update_refund_end_time.success', array());
        }catch(\Exception $e) {
            $this->getLogService()->error('order', 'update_refund_end_time', 'order.update_refund_end_time.error', array(
                'errorMsg' => ExceptionPrintingToolkit::printTraceAsArray($e)
            ));
        }
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

}