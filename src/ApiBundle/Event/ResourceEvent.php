<?php

namespace  ApiBundle\Event;

use ApiBundle\Api\Resource\ResourceProxy;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class ResourceEvent extends Event
{
    private $request;

    private $resourceProxy;

    public function __construct(Request $request, ResourceProxy $resourceProxy)
    {
        $this->request = $request;
        $this->resourceProxy = $resourceProxy;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return \ApiBundle\Api\Resource\ResourceProxy
     */
    public function getResourceProxy()
    {
        return $this->resourceProxy;
    }

    /**
     * @param \ApiBundle\Api\Resource\ResourceProxy $resourceProxy
     */
    public function setResourceProxy($resourceProxy)
    {
        $this->resourceProxy = $resourceProxy;
    }
}
