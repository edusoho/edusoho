<?php

namespace ApiBundle\Api\Resource;

class ResourceProxy
{
    private $resource;

    private $fieldFilters;

    private $fieldFilterFactory;

    public function __construct(FieldFilterFactory $factory, AbstractResource $resource)
    {
        $this->resource = $resource;
        $this->fieldFilterFactory = $factory;
    }

    public function __call($method, $arguments)
    {
        $result = call_user_func_array([$this->resource, $method], $arguments);
        if (in_array($method, $this->resource->supportMethods()) && $this->getFieldFilter($method)) {
            $this->filterResult($method, $result);
        }

        return $result;
    }

    private function getFieldFilter($method)
    {
        if (empty($this->fieldFilters[$method])) {
            $this->fieldFilters[$method] = $this->fieldFilterFactory->createFilter($this->resource, $method);
        }

        return $this->fieldFilters[$method];
    }

    public function getResource()
    {
        return $this->resource;
    }

    private function filterResult($method, &$result)
    {
        if (AbstractResource::METHOD_SEARCH == $method) {
            $this->getFieldFilter($method)->filters($result);
        } elseif (AbstractResource::METHOD_REMOVE != $method) {
            $this->getFieldFilter($method)->filter($result);
        }
    }
}
