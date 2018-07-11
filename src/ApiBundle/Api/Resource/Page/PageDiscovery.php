<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use AppBundle\Common\ArrayToolkit;
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
        
        $hotCourseList = $this->findCoursesAndCourseSetsBySort(
            array('hotSeq' => 'DESC', 'studentNum' => 'DESC', 'id' => 'DESC')
        );
        $recommendedCourseList = $this->findCoursesAndCourseSetsBySort(
            array('recommendedSeq' => 'DESC', 'recommendedTime' => 'DESC', 'id' => 'DESC')
        );

        $posters = $this->getBlockService()->getPosters();

        $result = array(
            array(
                'type' => 'slide_show', 
                'data' => $posters,
            ),
            array(
                'type' => 'course_list', 
                'data' => array(
                    array('title' => '热门课程', 'items' => $hotCourseList, 'source' => 
                        array('categoryId' => 0, 'courseType' => 'all', 'sort' => 'hitNum')
                    ),
                    array('title' => '推荐课程', 'items' => $recommendedCourseList, 'source' => 
                        array('categoryId' => 0, 'courseType' => 'all', 'sort' => 'recommendedSeq')
                    ),
                ),
            ),
        );
        return $result;
    }

    protected function findCoursesAndCourseSetsBySort($sort)
    {
        $conditions = array('parentId' => 0, 'status' => 'published', 'excludeTypes' => array('reservation'));
        $courseSets = $this->getCourseSetService()->searchCourseSets($conditions, $sort, 0, 4);
        $courses = $this->getCourseService()->findCoursesByCourseSetIds(ArrayToolkit::column($courseSets, 'id'));
        $courses = $this->getCourseService()->fillCourseTryLookVideo($courses);

        return array('courses' => $courses, 'courseSets' => $courseSets);
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