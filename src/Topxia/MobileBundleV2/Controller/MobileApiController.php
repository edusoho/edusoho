<?php

namespace Topxia\MobileBundleV2\Controller;

use Symfony\Component\HttpFoundation\Request;

class MobileApiController extends MobileBaseController
{
    private static $filtetNames = array(
            1000 => 'UrlFilter',
            999 => 'ServiceFilter',
        );

    private $filters = array();
    private $pathInfo;
    private $filterResponse;
    public $request;

    public function __construct()
    {
        ksort(self::$filtetNames);
        $namespace = 'Topxia\\MobileBundleV2\\Filter\\';
        foreach (self::$filtetNames as $key) {
            $class = $namespace.$key;
            $this->filters[$key] = new $class();
        }
    }

    private function filter($service, $method)
    {
        while ($filter = array_pop($this->filters)) {
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
        $this->request = $request;
        $this->pathInfo = $request->getPathInfo();
        if ($request->getMethod() == 'POST') {
            $this->formData = $request->request->all();
        } else {
            $this->formData = $request->query->all();
        }
    }

    public function indexAction(Request $request, $service, $method)
    {
        $this->init($request);
        if ($this->filter($service, $method)) {
            return $this->createJson($request, $this->getFilterResponse());
        }
        $class = $this->getClassName($service);

        if (!class_exists($class)) {
            return $this->createJson($request, 'service not exists');
        }

        $instance = call_user_func(array($class, 'getInstance'), $class, $this);
        $result = call_user_func(array($instance, $method));

        return $this->createResponse($request, $result);
    }

    private function createResponse($request, $result)
    {
        if (is_a($result, "Symfony\Component\HttpFoundation\Response")) {
            return $result;
        }

        return $this->createJson($request, $result);
    }

    private function getClassName($name)
    {
        $namespace = __NAMESPACE__;

        return 'Topxia\\MobileBundleV2\\Processor\\Impl\\'.$name.'ProcessorImpl';
    }
}
