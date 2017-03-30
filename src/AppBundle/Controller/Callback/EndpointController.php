<?php

namespace AppBundle\Controller\Callback;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Controller\BaseController;

class EndpointController extends BaseController
{
    public function publishAction(Request $request, $type)
    {
        $callbacks = $this->get('extension.manager')->getCallbacks();
        $biz = $this->getBiz();
        $processerInstance = $biz[$callbacks[$type]];

        return new JsonResponse($processerInstance->execute($request));
    }
}
