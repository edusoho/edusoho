<?php

namespace ApiBundle\Api;

use ApiBundle\Api\Exception\ApiNotFoundException;
use ApiBundle\Api\Resource\ResourceManager;
use Symfony\Component\HttpFoundation\Request;

class ResourceKernel
{
    private $pathParser;

    private $resManager;

    public function __construct(PathParser $pathParser , ResourceManager $resManager)
    {
        $this->pathParser = $pathParser;
        $this->resManager = $resManager;
    }

    public function handle(Request $request)
    {
        $pathMeta = $this->pathParser->parse($request);
        $resource = $this->resManager->create($pathMeta);

        return $this->invoke($request, $resource, $pathMeta);
    }

    private function invoke($request, $resource, PathMeta $pathMeta)
    {
        $resMethod = $pathMeta->getResMethod();

        if (!is_callable(array($resource, $resMethod))) {
            throw new ApiNotFoundException('Method does not exist');
        }

        $params = array_merge(array($request), $pathMeta->getSlugs());
        return call_user_func_array(array($resource, $resMethod), $params);
    }
}