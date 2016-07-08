<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Analysis extends BaseResource
{
    public function get(Application $app, Request $request, $type, $tab)
    {
        $class = $this->getClassName($type);
            
        if (!class_exists($class)) {
            throw $this->createNotFoundException('service not exists');
        }

        $instance = new $class($request);
        $result = call_user_func(array($instance, $tab));

        return $result;
    }

    private function getClassName($name)
    {
        return __NAMESPACE__ . "\AnalysisType\\{$name}";
    }

    public function filter($res)
    {
        if (strpos($res['avatar'], "files/") !== false) {
            $res['avatar'] = $this->getFileUrl($res['avatar']);
            return $res;
        }
        $res['avatar'] = $this->getAssetUrl($res['avatar']);
        return $res;
    }
}
