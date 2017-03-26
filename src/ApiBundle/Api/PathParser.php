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

        $pathExplode = explode('/', str_replace(ApiBundle::API_PREFIX, '', $pathInfo));
        //默认第一个是资源名称
        $nextIsResName = 1;
        foreach ($pathExplode as $part) {
            if ($part == '') {
                continue;
            }

            if ($nextIsResName) {
                $pathMeta->addResName($part);
                $nextIsResName = 0;
            } else {
                $pathMeta->addSlug($part);
                $nextIsResName = 1;
            }
        }
    }
}