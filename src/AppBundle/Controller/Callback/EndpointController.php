<?php

namespace AppBundle\Controller\Callback;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Controller\BaseController;

class EndpointController extends BaseController
{
    public function publishAction(Request $request, $resource)
    {
        $resourceInstance = $this->get('callback.resource_factory')->create($resource);
        $method = strtolower($request->getMethod());
        if (!in_array($method, array('post', 'get'))) {
            throw new \InvalidArgumentException(sprintf('unsupported method: %s', $method));
        }
        $resourceInstance->auth($request);

        return new JsonResponse($resourceInstance->$method($request));
    }
}
