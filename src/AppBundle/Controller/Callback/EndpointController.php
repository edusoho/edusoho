<?php

namespace AppBundle\Controller\Callback;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Controller\BaseController;

class EndpointController extends BaseController
{
    public function publishAction(Request $request, $type)
    {
        $processerInstance = $this->get('callback.processor_factory')->create($type);

        return new JsonResponse($processerInstance->execute($request));
    }
}
