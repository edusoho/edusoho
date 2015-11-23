<?php
namespace Mooc\WebBundle\Processor\Impl;

use Topxia\MobileBundleV2\Processor\Impl\SchoolProcessorImpl;

class MoocSchoolProcessorImpl extends SchoolProcessorImpl
{
    public function getLatestCourses()
    {
        $conditions = array(
            'parentId' => 0,
            'status'   => 'published'
        );
        return $this->getCourseByType("latest", $conditions);
    }

    public function getRecommendCourses()
    {
        $conditions = array(
            'parentId'    => 0,
            'status'      => 'published',
            "recommended" => 1
        );
        return $this->getCourseByType("recommendedSeq", $conditions);
    }

    public function getSchoolSite()
    {
        $result           = parent::getSchoolSite();
        $result['server'] = 'mooc';
        return $result;
    }

    private function getCourseByType($sort, $conditions)
    {
        $start = (int) $this->getParam("start", 0);
        $limit = (int) $this->getParam("limit", 10);

        $total   = $this->getCourseService()->searchCourseCount($conditions);
        $courses = $this->controller->getCourseService()->searchCourses($conditions, $sort, $start, $limit);
        $result  = array(
            "start" => $start,
            "limit" => $limit,
            "total" => $total,
            "data"  => $this->controller->filterCourses($courses));

        return $result;
    }
}
