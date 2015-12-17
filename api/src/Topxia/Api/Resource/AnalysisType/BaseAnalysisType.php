<?php

namespace Topxia\Api\Resource\AnalysisType;

use Silex\Application;
use Topxia\Service\Common\ServiceKernel;

class BaseAnalysisType
{
	protected $request;

	public function __construct($request)
	{
		$this->request = $request;
	}

	protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function error($code, $message)
    {
        return array('error' => array(
                'code' => $code,
                'message' => $message,
            ));
    }

    protected function getCurrentUser()
    {
        return $this->getServiceKernel()->getCurrentUser();
    }
}