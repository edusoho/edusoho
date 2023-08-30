<?php

namespace AppBundle\Controller\Cashier;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class CallbackController extends BaseController
{
    public function notifyAction(Request $request, $payment)
    {
        $targetCallback = $this->getTargetCallback($payment);

        $this->getLogger()->info('pay notify success '.$payment, $request->request->all());

        return $this->forward($targetCallback['notify'], [
            'request' => $request,
            'payment' => $payment,
        ]);
    }

    public function returnForH5Action(Request $request, $payment)
    {
        $targetCallback = $this->getTargetCallback($payment);

        return $this->forward($targetCallback['returnForH5'], [
            'request' => $request,
            'payment' => $payment,
        ]);
    }

    public function returnForAppAction(Request $request, $payment)
    {
        $targetCallback = $this->getTargetCallback($payment);

        return $this->forward($targetCallback['returnForApp'], [
            'request' => $request,
            'payment' => $payment,
        ]);
    }

    public function returnAction(Request $request, $payment)
    {
        $targetCallback = $this->getTargetCallback($payment);

        $this->getLogger()->info('pay return success '.$payment, $request->request->all());

        return $this->forward($targetCallback['return'], [
            'request' => $request,
            'payment' => $payment,
        ]);
    }

    protected function getTargetCallback($payment)
    {
        $payments = $this->get('extension.manager')->getPayments();
        if (!empty($payments[$payment])) {
            return $payments[$payment];
        }

        return null;
    }

    private function getLogger()
    {
        return $this->getBiz()['logger'];
    }
}
