<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Api\Util\MobileSchoolUtil;

class App extends BaseResource
{
    public function get(Application $app, Request $request, $id)
    {
        $schoolUtil = new MobileSchoolUtil();
        $app = $schoolUtil->findSchoolAppById($id);

        return $this->filter($app);
    }

    public function filter($res)
    {
        if (strpos($res['avatar'], 'files/') !== false) {
            $res['avatar'] = $this->getFileUrl($res['avatar']);

            return $res;
        }
        $res['avatar'] = $this->getAssetUrl($res['avatar']);

        return $res;
    }
}
