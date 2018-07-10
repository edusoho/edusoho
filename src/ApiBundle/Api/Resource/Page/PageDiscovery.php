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
        
        $hotCourseList = $this->getCourseSetService()->findCourseSetsbyOrder('hotSeq');
        $recommendedCourseList = $this->getCourseSetService()->findCourseSetsbyOrder(
            array('recommendedSeq' => 'DESC', 'recommendedTime' => 'DESC')
        );
        $posters = $this->getBlockService()->getPosters();
        $hotCourseList = $this->getFullImagePath($hotCourseList);
        $recommendedCourseList = $this->getFullImagePath($recommendedCourseList);
        $posters = $this->getFullImagePath($posters);
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
                    )
                )
            )
        );
        return $result;
    }

    protected function getFullImagePath($data)
    {
        foreach ($data as &$items) {
            if (0 === strpos($items['image'], '/assets') || 0 === strpos($items['image'], '/themes')) {
                $items['image'] = AssetHelper::uriForPath($items['image']);
            } else {
                $items['image'] = AssetHelper::getFurl(empty($items['image']) ? '' : $items['image'], 'course.png');
            }
        }
        return $data;
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