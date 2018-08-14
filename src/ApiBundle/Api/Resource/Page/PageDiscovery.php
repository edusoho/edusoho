<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PageDiscovery extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $portal)
    {
        if (!in_array($portal, array('h5', 'miniprogram'))) {
            throw new BadRequestHttpException('Portal is error', null, ErrorCode::INVALID_ARGUMENT);
        }

        $hotCourses = $this->findCoursesAndCourseSetsBySort(
            array('hitNum' => 'DESC', 'studentNum' => 'DESC', 'id' => 'DESC')
        );
        $recommendedCourses = $this->findCoursesAndCourseSetsBySort(
            array('recommendedSeq' => 'ASC', 'recommendedTime' => 'DESC', 'id' => 'DESC')
        );

        $posters = $this->getBlockService()->getPosters();

        $result = array(
            array(
                'type' => 'slide_show',
                'moduleType' => 'slide',
                'data' => $posters,
            ),
            array(
                'type' => 'course_list',
                'moduleType' => 'hotCourseList',
                'data' => array('title' => '热门课程', 'items' => $hotCourses, 'source' => array('category' => 0, 'courseType' => 'all', 'sort' => '-hitNum'),
                ),
            ),
            array(
                'type' => 'course_list',
                'moduleType' => 'recommendedCourseList',
                'data' => array('title' => '推荐课程', 'items' => $recommendedCourses, 'source' => array('category' => 0, 'courseType' => 'all', 'sort' => '-recommendedSeq'),
                ),
            ),
        );

        return $result;
    }

    protected function findCoursesAndCourseSetsBySort($sort)
    {
        $conditions = array('parentId' => 0, 'status' => 'published', 'excludeTypes' => array('reservation'));
        if (array_key_exists('recommendedSeq', $sort)) {
            $courses = $this->getCourseService()->searchCourseByRecommendedSeq($conditions, $sort, 0, 4);
        } else {
            $courses = $this->getCourseService()->searchCourses($conditions, $sort, 0, 4);
        }
        $this->getOCUtil()->multiple($courses, array('creator', 'teacherIds'));
        $this->getOCUtil()->multiple($courses, array('courseSetId'), 'courseSet');

        return $courses;
    }

    /**
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return \Biz\Course\Service\CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }

    /**
     * @return \Biz\Content\Service\BlockService
     */
    protected function getBlockService()
    {
        return $this->service('Content:BlockService');
    }
}
