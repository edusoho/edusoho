<?php

namespace AppBundle\Controller\Callback;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class CallbackController extends BaseController
{
    public function notifyAction(Request $request)
    {
        $type = $request->query->get('type');

        $targetCallback = $this->getCallbackService()->getCallbackType($type);
        $controller = $targetCallback->forwardController;

        return $this->forward($controller.':notify',array(
            'request' => $request
        ));
    }

    protected function getCallbackService()
    {
        return $this->createService('Callback:CallbackService');
    }
}