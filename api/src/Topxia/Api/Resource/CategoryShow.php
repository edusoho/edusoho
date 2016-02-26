<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class CategoryShow extends BaseResource
{

    public function get(Application $app, Request $request)
    {
    	$result = $this->getMobileShowService()->getAllMobileShows();
    	if (empty($result)) {

    	}

    	return $result;
    }
}