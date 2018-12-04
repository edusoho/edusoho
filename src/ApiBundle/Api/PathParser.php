<?php

namespace ApiBundle\Api;

use ApiBundle\ApiBundle;

class PathParser
{
    public function parse(ApiRequest $request)
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

            if ($part == 'me') {
                $pathMeta->addResName($part);
                continue;
            }

            if ($part == 'app') {
                $pathMeta->addResName($part);
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
