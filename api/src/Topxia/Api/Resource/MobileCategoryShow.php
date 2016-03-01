<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class MobileCategoryShow extends BaseResource
{

    public function get(Application $app, Request $request)
    {
    	$result = $this->getMobileShowService()->getAllMobileShows();
    	if (empty($result)) {

    	}

    	return $result;
    }

	public function filter(&$res)
    {
        
    }

    protected function getMobileShowService()
    {
    	return $this->getServiceKernel()->createService('MobileShow.MobileShowService');
    }
}