<?php

namespace Mooc\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\MobileBundleV2\Controller\MobileBaseController;

class MobileApiController extends MobileBaseController
{
    private static $filtetNames = array(
        1000 => "UrlFilter",
        999  => "ServiceFilter"
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
            $class               = $namespace.$key;
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
        $this->request  = $request;
        $this->pathInfo = $request->getPathInfo();

        if ($request->getMethod() == "POST") {
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
            return $this->createJson($request, "service not exists");
        }

        $instance = call_user_func(array($class, "getInstance"), $class, $this);
        $result   = call_user_func(array($instance, $method));
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
        $class = 'Mooc\\WebBundle\\Processor\\Impl\\Mooc'.$name.'ProcessorImpl';

        if (class_exists($class)) {
            return $class;
        }

        return 'Topxia\\MobileBundleV2\\Processor\\Impl\\'.$name.'ProcessorImpl';
    }

    public function filterCourses($courses)
    {
        if (empty($courses)) {
            return array();
        }

        $teacherIds = array();

        foreach ($courses as $course) {
            $teacherIds = array_merge($teacherIds, $course['teacherIds']);
        }

        $teachers = $this->getUserService()->findUsersByIds($teacherIds);
        $teachers = $this->simplifyUsers($teachers);

        $coinSetting = $this->getCoinSetting();
        $self        = $this;
        $container   = $this->container;
        return array_map(function ($course) use ($self, $container, $teachers, $coinSetting) {
            $course['smallPicture']  = $container->get('topxia.twig.web_extension')->getFilePath($course['smallPicture'], 'course-large.png', true);
            $course['middlePicture'] = $container->get('topxia.twig.web_extension')->getFilePath($course['middlePicture'], 'course-large.png', true);
            $course['largePicture']  = $container->get('topxia.twig.web_extension')->getFilePath($course['largePicture'], 'course-large.png', true);
            $course['about']         = $self->convertAbsoluteUrl($container->get('request'), $course['about']);
            $course['createdTime']   = date("c", $course['createdTime']);
            $course['startTime']     = date("c", $course['startTime']);
            $course['endTime']       = date("c", $course['endTime']);
            $course['now']           = date("c", time());

            $course['teachers'] = array();

            foreach ($course['teacherIds'] as $teacherId) {
                if (isset($teachers[$teacherId])) {
                    $course['teachers'][] = $teachers[$teacherId];
                }
            }

            unset($course['teacherIds']);
            $course["priceType"] = $coinSetting["priceType"];
            $course['coinName']  = $coinSetting["name"];
            return $course;
        }, $courses);
    }
}
