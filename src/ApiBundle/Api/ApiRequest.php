<?php

namespace ApiBundle\Api;

use Symfony\Component\HttpFoundation\ParameterBag;

class ApiRequest
{
    /**
     * @var ParameterBag
     */
    public $query;

    /**
     * @var ParameterBag
     */
    public $request;

    private $pathInfo;

    private $method;

    public function __construct($pathInfo, $method, $query, $request)
    {
        $this->pathInfo = $pathInfo;
        $this->method = $method;

        if ($query instanceof ParameterBag) {
            $this->query = $query;
        } else {
            $this->query = new ParameterBag($query);
        }

        if ($request instanceof ParameterBag) {
            $this->request = $request;
        } else {
            $this->request = new ParameterBag($request);
        }
    }

    public function getPathInfo()
    {
        return $this->pathInfo;
    }

    public function getMethod()
    {
        return $this->method;
    }
}