<?php

namespace AppBundle\Controller\Callback;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Controller\BaseController;

class IndexController extends BaseController
{
    public function indexAction(Request $request, $resource)
    {
        $resourceInstance = $this->get('callback.resource_factory')->create($resource);
        $method = strtolower($request->getMethod());
        if (!in_array($method, array('post', 'get'))) {
            throw new \InvalidArgumentException(sprintf('unsupported method: %s', $method));
        }

        return new JsonResponse($resourceInstance->$method($request));
    }
}
