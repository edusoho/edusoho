<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class CourseReviewDataTag extends CourseBaseDataTag implements DataTag  
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
        $this->checkReviewId($arguments);

    	$courseReview = $this->getReviewService()->getReview($arguments['reviewId']);
        $courseReview['reviewer'] = $this->getUserService()->getUser($courseReview['userId']);
        $Reviewer = &$courseReview['reviewer'];
        unset($Reviewer['password']);
        unset($Reviewer['salt']);
        $courseReview['course'] = $this->getCourseService()->getCourse($courseReview['courseId']);

        return $courseReview;

    }

    
}
