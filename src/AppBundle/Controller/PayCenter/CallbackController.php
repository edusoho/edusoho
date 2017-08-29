<?php

namespace AppBundle\Controller\PayCenter;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class CallbackController extends BaseController
{
    public function notifyAction(Request $request, $type)
    {
        $targetCallback = $this->getTargetCallback($type);

        return $this->forward($targetCallback['notifyController'].':notify', array(
            'request' => $request,
            'type' => $type
        ));
    }

    protected function getTargetCallback($type)
    {
        $payments = $this->get('extension.manager')->getPayments();
        return $payments[$type];
    }
}
