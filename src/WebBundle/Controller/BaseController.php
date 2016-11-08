<?php

namespace WebBundle\Controller;


use Codeages\Biz\Framework\Service\BaseService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Topxia\Common\Exception\ResourceNotFoundException;

class BaseController extends Controller
{
    protected function getBiz()
    {
        return $this->get('biz');
    }

    public function getUser()
    {
        $biz = $this->getBiz();
        return $biz['user'];
    }

    protected function createJsonResponse($data = null, $status = 200, $headers = array())
    {
        return new JsonResponse($data, $status, $headers);
    }

    protected function createJsonpResponse($data = null, $callback='callback', $status=200, $headers=array())
    {
        $response = $this->createJsonResponse($data, $status, $headers);
        return $response
            ->setCallback($callback)
            ;
    }

    protected function createResourceNotFoundException($resourceType, $resourceId, $message='')
    {
        return new ResourceNotFoundException($resourceType, $resourceId, $message);
    }

    /**
     * @param string $alias
     * @return BaseService
     */
    protected function createService($alias)
    {
        $biz = $this->getBiz();
        return $biz->service($alias);
    }
}