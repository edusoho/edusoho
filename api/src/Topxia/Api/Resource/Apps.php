<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Api\Util\MobileSchoolUtil;
use Symfony\Component\HttpFoundation\Request;

class Apps extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $schoolUtil = new MobileSchoolUtil();
        $apps = $schoolUtil->searchSchoolApps();

        return $this->filter($apps);
    }

    public function filter($res)
    {
        return $this->multicallFilter('App', $res);
    }
}
