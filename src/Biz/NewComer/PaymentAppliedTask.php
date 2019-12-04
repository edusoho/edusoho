<?php

namespace Biz\NewComer;

class PaymentAppliedTask extends BaseNewcomer
{
    public function getStatus()
    {
        $newcomerTask = $this->getSettingService()->get('newcomer_task', array());

        if (!empty($newcomerTask['payment_applied_task']['status'])) {
            return true;
        }

        $payment = $this->getSettingService()->get('payment', array());
        if (!empty($payment['alipay_enabled']) || !empty($payment['wxpay_enabled']) || !empty($payment['llpay_enabled'])) {
            $this->doneTask('payment_applied_task');

            return true;
        }

        return false;
    }
}
