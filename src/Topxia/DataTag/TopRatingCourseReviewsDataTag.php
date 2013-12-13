<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class TopRatingCourseReviewsDataTag extends BaseDataTag implements DataTag  
{
    /**
     * 获取按照评分排行的课程评论
     *
     * 可传入的参数：
     *   courseId 必需 课程ID
     *   count 必需 课程话题数量，取值不能超过100
     * 
     * @param  array $arguments 参数
     * @return array 课程评论
     */

    public function getData(array $arguments)
    {
        if (empty($arguments['courseId'])) {
            throw new \InvalidArgumentException("courseId参数缺失");
        }
        if (empty($arguments['count'])) {
            throw new \InvalidArgumentException("count参数缺失");
        }
        if ($arguments['count'] > 100) {
            throw new \InvalidArgumentException("count参数超出最大取值范围");
        }
        $conditions = array( 'courseId' => $arguments['courseId']);
    	return $this->getReviewService()->searchReviews($conditions, $sort = 'Rating', 0, $arguments['count']);
    }

    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }

}


?>