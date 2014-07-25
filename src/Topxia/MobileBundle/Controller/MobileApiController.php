<?php

namespace Topxia\MobileBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class MobileApiController extends MobileBaseController
{
	    private static $filtetNames = array(
	    	1000=>"UrlFilter",
	    	999=>"ServiceFilter"
	    );

	    private $filters = array();
	    private $pathInfo;
	    private $filterResponse;

	    function __construct(){
	    	ksort(self::$filtetNames);
	            $namespace = 'Topxia\\MobileBundle\\Filter\\';
	            foreach (self::$filtetNames as $key) {
	            	$class = $namespace . $key;
	            	$this->filters[$key] = new $class();
	            }
	    }

	    private function filter($service, $method)
	    {
	    	foreach ($this->filters as $filter) {
	    		if (!$this->isSatisfy($filter)) {
	    			continue;
	    		}

	    		$filterResult = $filter->invoke($this->formData, $service, $method);
	    		if ($filterResult->hasFilter()) {
	    			$this->setFilterResponse($filterResult->resultData);
	    			return true;
	    		}
	    		if ($filterResult->hasNext()) {
	    			continue;
	    		}
	    		return false;
	            }
	            return false;
	    }

	    private function setFilterResponse($resultData)
	    {
	    	$this->filterResponse = $resultData;
	    }

	    private function getFilterResponse()
	    {
	    	return $this->filterResponse;
	    }

	    private function isSatisfy($filter)
	    {
	    	$match = preg_match($filter->filterUrl, $this->pathInfo, $matches);
	    	return !empty($match);
	    }

	    private function init($request)
	    {
	    	$this->pathInfo = $request->getPathInfo();
	    	$this->formData = $request->request->all();
	    }

	    public function IndexAction(Request $request, $service, $method)
	    {
	    	$this->init($request);
	    	if ($this->filter($service, $method) ){
	    		return $this->createJson($request, $this->getFilterResponse());
	    	}
	    	$class = $this->getClassName($service);
	    	
	    	if (!class_exists($class)) {
	    		return $this->createJson($request, "service not exists");
	    	}

	    	$instance = new $class($this->formData);
	    	if (method_exists($instance, $method)) {
	    		$result = call_user_func(array($instance, $method));
	    	} else {
	    		$result = "method not exists";
	    	}
	    	return $this->createJson($request, $result);
	    }

	    private function after()
	    {

	    }

	    private function before()
	    {

	    }

	    private function getClassName($name)
	    {
	    	$namespace = __NAMESPACE__;
	    	return 'Topxia\\MobileBundle\\Service\\Impl\\' . $name . 'ServiceImpl';
	    }
}