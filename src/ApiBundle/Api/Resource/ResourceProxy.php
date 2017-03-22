<?php

namespace ApiBundle\Api\Resource;

use ApiBundle\Api\PathMeta;
use Codeages\Biz\Framework\Context\Biz;

class ResourceProxy
{
    private $resource;

    private $proxyMethods = array(
        Resource::METHOD_CREATE,
        Resource::METHOD_GET,
        Resource::METHOD_SEARCH,
        Resource::METHOD_UPDATE
    );

    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    public function __call($method, $arguments)
    {
        $result = call_user_func_array(array($this->resource, $method), $arguments);
        if (in_array($method, $this->proxyMethods) && $this->resource->getFilter()) {
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