<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use Biz\Course\CourseSetException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;

class CourseSet extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        if (empty($courseSet)) {
            throw CourseSetException::NOTFOUND_COURSESET();
        }

        $this->getOCUtil()->single($courseSet, array('creator', 'teacherIds'));

        $this->appendMaxOriginPriceAndMinOriginPrice($courseSet);

        return $courseSet;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $conditions['status'] = 'published';
        $conditions['showable'] = 1;
        $conditions['parentId'] = 0;
        //过滤约排课
        $conditions['excludeTypes'] = array('reservation');
        if (isset($conditions['type']) && 'all' == $conditions['type']) {
            unset($conditions['type']);
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $sort = $this->getSort($request);

        if (array_key_exists('recommendedSeq', $sort)) {
            $courseSets = $this->getRecommendedSeq($conditions, $sort, $offset, $limit);
        } else {
            $courseSets = $this->getCourseSetService()->searchCourseSets(
                $conditions,
                $sort,
                $offset,
                $limit
            );
        }

        $this->getOCUtil()->multiple($courseSets, array('creator', 'teacherIds'));

        $total = $this->getCourseSetService()->countCourseSets($conditions);

        return $this->makePagingObject($courseSets, $total, $offset, $limit);
    }

    private function getRecommendedSeq($conditions, $sort, $offset, $limit)
    {
        $conditions['recommended'] = 1;
        $recommendCount = $this->getCourseSetService()->countCourseSets($conditions);
        $recommendAvailable = $recommendCount - $offset;
        $courseSets = array();

        if ($recommendAvailable >= $limit) {
            $courseSets = $this->getCourseSetService()->searchCourseSets(
                $conditions,
                $sort,
                $offset,
                $limit
            );
        }

        if ($recommendAvailable <= 0) {
            $conditions['recommended'] = 0;
            $courseSets = $this->getCourseSetService()->searchCourseSets(
                $conditions,
                array('createdTime' => 'DESC'),
                abs($recommendAvailable),
                $limit
            );
        }

        if ($recommendAvailable > 0 && $recommendAvailable < $limit) {
            $courseSets = $this->getCourseSetService()->searchCourseSets(
                $conditions,
                $sort,
                $offset,
                $recommendAvailable
            );
            $conditions['recommended'] = 0;
            $coursesTemp = $this->getCourseSetService()->searchCourseSets(
                $conditions,
                array('createdTime' => 'DESC'),
                0,
                $limit - $recommendAvailable
            );
            $courseSets = array_merge($courseSets, $coursesTemp);
        }

        return $courseSets;
    }

    private function appendMaxOriginPriceAndMinOriginPrice(&$courseSet)
    {
        $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);

        $maxOriginPrice = 0;
        $minOriginPrice = 0;
        foreach ($courses as $course) {
            if ('published' != $course['status']) {
                continue;
            }

            if ($course['originPrice'] > $maxOriginPrice) {
                $maxOriginPrice = $course['originPrice'];
            }

            if (!$minOriginPrice) {
                $minOriginPrice = $course['originPrice'];
            }

            if ($course['originPrice'] < $minOriginPrice) {
                $minOriginPrice = $course['originPrice'];
            }
        }

        $courseSet['maxOriginPrice'] = $maxOriginPrice;
        $courseSet['minOriginPrice'] = $minOriginPrice;
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }
}
