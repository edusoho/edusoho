<?php

namespace Topxia\Api\Resource;

use Topxia\Service\Common\ServiceKernel;

abstract class BaseResource
{
    abstract function filter(&$res);

    protected function callFilter($name, &$res)
    {
        global $app;
        return $app["res.{$name}"]->filter($res);
    }

    protected function multicallFilter($name, &$res)
    {
        foreach ($res as &$one) {
            $this->callFilter($name, $one);
        }
        return $res;
    }

    protected function callSimplify($name, &$res)
    {
        global $app;
        return $app["res.{$name}"]->simplify($res);
    }

    protected function simplify($res)
    {
        return $res;
    }

    protected function wrap($resources, $total)
    {
        return array('resources' => $resources, 'total' => $total);
    }


    protected function filterHtml($text)
    {
        preg_match_all('/\<img.*?src\s*=\s*[\'\"](.*?)[\'\"]/i', $text, $matches);
        if (empty($matches)) {
            return $text;
        }

        foreach ($matches[1] as $url) {
            $text = str_replace($url, $this->getFileUrl($url), $text);
        }

        return $text;
    }

    protected function getFileUrl($path)
    {
        if (empty($path)) {
            return '';
        }
        if (strpos($path, "http://") !== false) {
            return $path;
        }
        $path = str_replace('public://', '', $path);
        $path = str_replace('files/', '', $path);
        $path = "http://{$_SERVER['HTTP_HOST']}/files/{$path}";

        return $path;
    }

    protected function getAssetUrl($path)
    {
        if (empty($path)) {
            return '';
        }
        $path = "http://{$_SERVER['HTTP_HOST']}/assets/{$path}";
        return $path;
    }

    protected function getCurrentUser()
    {
        return $this->getServiceKernel()->getCurrentUser();
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

}