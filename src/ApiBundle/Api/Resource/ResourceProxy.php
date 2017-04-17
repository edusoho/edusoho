<?php

namespace ApiBundle\Api\Resource;

use ApiBundle\Api\FieldFilterFactory;

class ResourceProxy
{
    /**
     * @var ApiBundle\Api\Resource\Resource
     */
    private $resource;

    private $fieldFilters;

    public function __construct(ApiBundle\Api\Resource\Resource $resource)
    {
        $this->resource = $resource;
    }

    public function __call($method, $arguments)
    {
        $result = call_user_func_array(array($this->resource, $method), $arguments);
        if (in_array($method, $this->resource->supportMethods()) && $this->getFieldFilter($method)) {
            $this->filterResult($method, $result);
        }

        return $result;
    }

    private function getFieldFilter($method)
    {
        if (empty($this->fieldFilters[$method])) {
            $this->fieldFilters[$method] = $this->getFieldFilterFactory()->createFilter($this->resource, $method);
        }

        return $this->fieldFilters[$method];
    }

    /**
     * @return FieldFilterFactory
     */
    private function getFieldFilterFactory()
    {
        $biz = $this->resource->getBiz();
        return $biz['api.field.filter.factory'];
    }

    /**
     * @var ApiBundle\Api\Resource\Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    private function filterResult($method, &$result)
    {
        if ($method == Resource::METHOD_SEARCH) {
            $this->getFieldFilter($method)->filters($result);
        } else {
            $this->getFieldFilter($method)->filter($result);
        }

    }

}