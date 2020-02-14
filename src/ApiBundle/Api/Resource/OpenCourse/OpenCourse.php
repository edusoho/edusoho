<?php

namespace ApiBundle\Api\Resource\OpenCourse;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\OpenCourse\Service\OpenCourseService;

class OpenCourse extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $total = $this->getOpenCourseService()->countLiveCourses($request->query->all());
        $courses = $this->getOpenCourseService()->searchAndSortLiveCourses($request->query->all(), array(), $offset, $limit);

        $this->getOCUtil()->multiple($courses, array('userId', 'teacherIds'));

        return $this->makePagingObject($courses, $total, $offset, $limit);
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->service('OpenCourse:OpenCourseService');
    }
}