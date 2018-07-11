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
        
        $hotCourseList = $this->getCourseService()->findCoursesByCourseSet('hotSeq');
        $recommendedCourseList = $this->getCourseService()->findCoursesByCourseSet(
            array('recommendedSeq' => 'DESC', 'recommendedTime' => 'DESC')
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

    /**
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return \Biz\Content\Service\BlockService
     */
    protected function getBlockService()
    {
        return $this->service('Content:BlockService');
    }
}