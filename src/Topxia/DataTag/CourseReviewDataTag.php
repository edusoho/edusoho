<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class CourseReviewDataTag extends BaseDataTag implements DataTag  
{
    /**
     * 获取一个课程评论
     *
     * 可传入的参数：
     *   reviewId 必需 课程评论ID
     * 
     * @param  array $arguments 参数
     * @return array 课程评论
     */
    
    public function getData(array $arguments)
    {
        if (empty($arguments['reviewId'])) {
            throw new \InvalidArgumentException("reviewId参数缺失");
        }
    	return $this->getReviewService()->getReview($arguments['reviewId']);
    }

    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }
}


?>