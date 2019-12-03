<?php

namespace Biz\NewComer;

class PaymentAppliedTask extends BaseNewcomer
{
    public function getStatus()
    {
        $payment = $this->getSettingService()->get('payment', array());

        if (!empty($payment['alipay_enabled'])) {
            return true;
        }

        if (!empty($payment['wxpay_enabled'])) {
            return true;
        }

        if (!empty($payment['llpay_enabled'])) {
            return true;
        }

        return false;
    }
}
