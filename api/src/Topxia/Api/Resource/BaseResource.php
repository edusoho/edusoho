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

    protected function wrap($resources, $total)
    {
        return array('resources' => $resources, 'total' => $total);
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