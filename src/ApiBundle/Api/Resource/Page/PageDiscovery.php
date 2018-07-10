<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ApiBundle\Api\Util\AssetHelper;

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
        
        $setting = array('hot_course_list', 'recommended_course_list');
        $hotCourseList = $this->getCourseSetService()->findCourseSetsbyOrder('hotSeq');
        $recommendedCourseList = $this->getCourseSetService()->findCourseSetsbyOrder(
            array('recommendedSeq' => 'DESC', 'recommendedTime' => 'DESC')
        );
        $hotCourseList = $this->getFullImagePath($hotCourseList);
        $recommendedCourseList = $this->getFullImagePath($recommendedCourseList);
        $result = array(
            'type' => 'course_list', 
            'data' => array(
                array('title' => '热门课程', 'items' => $hotCourseList, 'source' => 
                    array('categoryId' => 0, 'courseType' => 'all', 'sort' => 'hitNum')
                ),
                array('title' => '推荐课程', 'items' => $recommendedCourseList, 'source' => 
                    array('categoryId' => 0, 'courseType' => 'all', 'sort' => 'recommendedSeq')
                )
            )
        );
        return $result;
    }

    protected function getFullImagePath($courseSets)
    {
        foreach ($courseSets as &$courseSet) {
            if (0 === strpos($courseSet['image'], '/assets')) {
                $courseSet['image'] = AssetHelper::uriForPath($courseSet['image']);
            } else {
                $courseSet['image'] = AssetHelper::getFurl(empty($courseSet['image']) ? '' : $courseSet['image'], 'course.png');
            }
        }
        return $courseSets;
    }

    /**
     * @return \Biz\Course\Service\CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }
}