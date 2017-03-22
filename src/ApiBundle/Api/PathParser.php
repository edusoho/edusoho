<?php

namespace ApiBundle\Api;

use ApiBundle\ApiBundle;
use Symfony\Component\HttpFoundation\Request;

class PathParser
{
    public function parse(Request $request)
    {
        $pathInfo = $request->getPathInfo();
        $pathMeta = new PathMeta();
        $this->parsePathInfo($pathMeta, $pathInfo);
        $pathMeta->setHttpMethod($request->getMethod());

        return $pathMeta;
    }

    private function parsePathInfo($pathMeta, $pathInfo)
    {

        $pathExplode = explode('/', ltrim($pathInfo, ApiBundle::API_PREFIX));

        foreach ($pathExplode as $part) {
            if ($part == '') {
                continue;
            }

            if (is_numeric($part)) {
                $pathMeta->addSlug($part);
            } else {
                $pathMeta->addResName($part);
            }

        }
    }
}