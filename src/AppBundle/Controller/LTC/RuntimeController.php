<?php

namespace AppBundle\Controller\LTC;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RuntimeController extends BaseController
{
    public function entryPointAction(Request $request, $endpoint)
    {
        $_API = new Api();
        $biz = $this->get('biz');
        $_DB = $biz['db'];

        $GLOBALS['_API'] = $_API;
        $GLOBALS['_DB'] = $_DB;

        $result = require "/usr/local/var/www/edusoho/learning_tools/server/{$endpoint}.php";

        return new Response($result);
    }
}
