<?php

namespace ApiBundle\Api\Resource;

class ResourceProxy
{
    private $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function __call($method, $arguments)
    {
        $result = call_user_func_array(array($this->resource, $method), $arguments);
        if (in_array($method, $this->resource->supportMethods()) && $this->resource->getFilter()) {
            $this->filterResult($method, $result);
        }

        return $result;
    }

    public function getResource()
    {
        return $this->resource;
    }

    private function filterResult($method, &$result)
    {
        if ($method == Resource::METHOD_SEARCH) {
            $this->resource->getFilter()->filters($result);
        } else {
            $this->resource->getFilter()->filter($result);
        }
    }

}