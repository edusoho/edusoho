<?php

namespace AppBundle\Controller\Cashier;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class CallbackController extends BaseController
{
    public function notifyAction(Request $request, $payment)
    {
        $targetCallback = $this->getTargetCallback($payment);

        return $this->forward($targetCallback['notify'], array(
            'request' => $request,
            'payment' => $payment,
        ));
    }

    public function returnForH5Action(Request $request, $payment)
    {
        $targetCallback = $this->getTargetCallback($payment);

        return $this->forward($targetCallback['returnForH5'], array(
            'request' => $request,
            'payment' => $payment,
        ));
    }

    public function returnForAppAction(Request $request, $payment)
    {
        $targetCallback = $this->getTargetCallback($payment);

        return $this->forward($targetCallback['returnForApp'], array(
            'request' => $request,
            'payment' => $payment,
        ));
    }

    public function returnAction(Request $request, $payment)
    {
        $targetCallback = $this->getTargetCallback($payment);

        return $this->forward($targetCallback['return'], array(
            'request' => $request,
            'payment' => $payment,
        ));
    }

    protected function getTargetCallback($payment)
    {
        $payments = $this->get('extension.manager')->getPayments();
        if (!empty($payments[$payment])) {
            return $payments[$payment];
        }

        return null;
    }
}
